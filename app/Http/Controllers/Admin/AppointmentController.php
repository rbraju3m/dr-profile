<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Chamber;
use App\Services\BookingService;
use App\Support\Like;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $appointments = $this->filtered($request)
            ->with('chamber', 'service')
            ->orderByDesc('appointment_date')
            ->orderBy('slot_time')
            ->paginate(25)
            ->withQueryString();

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'chambers' => Chamber::ordered()->get()->mapWithKeys(fn (Chamber $c) => [$c->id => $c->name]),
            'filters' => $this->filters($request),
        ]);
    }

    public function show(Appointment $appointment): View
    {
        return view('admin.appointments.show', [
            'appointment' => $appointment->load('chamber', 'service', 'schedule'),
        ]);
    }

    public function edit(Appointment $appointment): View
    {
        return view('admin.appointments.edit', [
            'appointment' => $appointment->load('chamber'),
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'patient_name' => ['required', 'string', 'max:120'],
            'patient_phone' => ['required', 'string', 'max:40'],
            'patient_email' => ['nullable', 'email', 'max:150'],
            'patient_gender' => ['nullable', 'in:male,female,other'],
            'patient_age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'patient_address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'patient_name' => __('site.booking.patient_name'),
            'patient_phone' => __('site.booking.patient_phone'),
            'patient_email' => __('site.booking.patient_email'),
            'patient_gender' => __('site.booking.patient_gender'),
            'patient_age' => __('site.booking.patient_age'),
            'patient_address' => __('site.booking.patient_address'),
            'notes' => __('admin.appointments.notes'),
            'admin_note' => __('admin.appointments.admin_note'),
        ]);

        $appointment->update($data);

        return redirect()
            ->route('admin.appointments.index')
            ->with('success', __('admin.flash.updated', ['item' => __('admin.nav.appointments')]));
    }

    public function updateStatus(Request $request, Appointment $appointment, BookingService $booking): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Appointment::STATUSES)],
            'cancelled_reason' => ['nullable', 'string', 'max:200'],
        ], [], [
            'status' => __('admin.appointments.status'),
            'cancelled_reason' => __('site.booking.cancel_reason'),
        ]);

        $booking->changeStatus($appointment, $data['status'], $data['cancelled_reason'] ?? null);

        return back()->with('success', __('admin.appointments.status_changed', [
            'status' => __('site.status.'.$data['status']),
        ]));
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $appointment->delete();

        return redirect()
            ->route('admin.appointments.index')
            ->with('success', __('admin.flash.deleted', ['item' => __('admin.nav.appointments')]));
    }

    /** Streams rather than buffers, so a year of appointments does not exhaust memory. */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($request)->with('chamber')->orderBy('appointment_date')->orderBy('slot_time');
        $filename = 'appointments-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM so Excel reads Bangla correctly

            // The file is English throughout, headers and all: it is opened in a
            // spreadsheet and passed around, not read inside the panel, so it
            // does not follow whichever language the operator happens to be in.
            fputcsv($handle, [
                'Serial', 'Date', 'Time', 'Status', 'Patient', 'Phone', 'Email',
                'Gender', 'Age', 'Visit', 'Chamber', 'Notes',
            ]);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $a) {
                    fputcsv($handle, array_map($this->inert(...), [
                        $a->appointment_no,
                        $a->appointment_date->toDateString(),
                        $a->slot_time,
                        $a->status,
                        $a->patient_name,
                        $a->patient_phone,
                        $a->patient_email,
                        $a->patient_gender,
                        $a->patient_age,
                        $a->visit_type,
                        $a->chamber?->name_en,
                        $a->notes,
                    ]));
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Stop a spreadsheet treating a patient's own words as a formula.
     *
     * Excel and Sheets run a cell that opens with =, +, - or @, and every name,
     * note and email in this file was typed by a member of the public. Somebody
     * booking as =HYPERLINK("http://…","Click") would otherwise have it run on
     * the machine of whoever opened the export. A leading apostrophe is the
     * spreadsheet's own way of saying "this is text"; it is not shown in the cell.
     */
    private function inert(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    private function filtered(Request $request)
    {
        $f = $this->filters($request);

        return Appointment::query()
            ->when($f['status'], fn ($q) => $q->where('status', $f['status']))
            ->when($f['chamber_id'], fn ($q) => $q->where('chamber_id', $f['chamber_id']))
            ->when($f['from'], fn ($q) => $q->whereDate('appointment_date', '>=', $f['from']))
            ->when($f['to'], fn ($q) => $q->whereDate('appointment_date', '<=', $f['to']))
            ->when($f['q'], function ($q) use ($f) {
                $term = Like::contains($f['q']);

                $q->where(function ($inner) use ($term) {
                    $inner->where('appointment_no', 'like', $term)
                        ->orWhere('patient_name', 'like', $term)
                        ->orWhere('patient_phone', 'like', $term);
                });
            });
    }

    private function filters(Request $request): array
    {
        return [
            'q' => $request->string('q')->trim()->toString(),
            'status' => in_array($request->string('status')->toString(), Appointment::STATUSES, true)
                ? $request->string('status')->toString()
                : '',
            'chamber_id' => $request->integer('chamber_id') ?: '',
            'from' => $request->date('from')?->toDateString() ?? '',
            'to' => $request->date('to')?->toDateString() ?? '',
        ];
    }
}
