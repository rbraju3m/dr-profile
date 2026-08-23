<?php

namespace App\Http\Requests;

use App\Models\Chamber;
use App\Models\Service;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'chamber_id' => [
                'required',
                Rule::exists('chambers', 'id')->where('is_active', true)->where('accepts_online_booking', true),
            ],
            'appointment_date' => ['required', 'date_format:Y-m-d'],
            'slot_time' => ['required', 'date_format:H:i:s'],
            'service_id' => ['nullable', Rule::exists('services', 'id')->where('is_active', true)],
            'patient_name' => ['required', 'string', 'min:3', 'max:120'],
            // Bangladeshi mobile numbers, with or without +88.
            'patient_phone' => ['required', 'string', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/'],
            'patient_email' => ['nullable', 'email:rfc', 'max:150'],
            'patient_gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'patient_age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'patient_address' => ['nullable', 'string', 'max:255'],
            'visit_type' => ['required', Rule::in(['new', 'followup'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'chamber_id' => __('site.booking.step_chamber'),
            'appointment_date' => __('site.booking.step_date'),
            'slot_time' => __('site.booking.step_slot'),
            'patient_name' => __('site.booking.patient_name'),
            'patient_phone' => __('site.booking.patient_phone'),
            'patient_email' => __('site.booking.patient_email'),
            'patient_gender' => __('site.booking.patient_gender'),
            'patient_age' => __('site.booking.patient_age'),
            'visit_type' => __('site.booking.visit_type'),
        ];
    }

    public function messages(): array
    {
        return [
            'patient_phone.regex' => __('validation_custom.phone'),
        ];
    }

    /**
     * Store one shape of the number, so the record reads the same however the
     * patient typed it. Anything Phone cannot recognise is left alone for the
     * rule below to reject, rather than mangled into a different complaint.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('patient_phone')) {
            $this->merge(['patient_phone' => Phone::canonical($this->input('patient_phone'))]);
        }
    }

    public function chamber(): Chamber
    {
        return Chamber::with('activeSchedules')->findOrFail($this->integer('chamber_id'));
    }

    public function service(): ?Service
    {
        return $this->filled('service_id') ? Service::find($this->integer('service_id')) : null;
    }
}
