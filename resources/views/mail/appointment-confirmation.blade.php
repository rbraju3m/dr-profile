@php
    $chamber = $appointment->chamber;
    $date = $appointment->appointment_date;
@endphp
<x-mail::message>
# {{ __('site.booking.success_heading') }}

{{ __('site.booking.success_text') }}

<x-mail::panel>
**{{ __('site.booking.serial') }}:** {{ $appointment->appointment_no }}
</x-mail::panel>

<x-mail::table>
| | |
|:--|:--|
| {{ __('site.booking.step_date') }} | {{ $date->format('l, d F Y') }} |
| {{ __('site.booking.step_slot') }} | {{ \Illuminate\Support\Carbon::parse($appointment->slot_time)->format('g:i A') }} |
| {{ __('site.booking.step_chamber') }} | {{ $chamber?->tr('name') }} |
| {{ __('site.chamber.address') }} | {{ $chamber?->tr('address') }} |
| {{ __('site.booking.patient_name') }} | {{ $appointment->patient_name }} |
</x-mail::table>

@if ($chamber?->tr('note'))
{{ $chamber->tr('note') }}
@endif

{{-- Both parameters are named: this view may render from a queue worker,
     where the locale URL-default set during a web request does not exist. --}}
<x-mail::button :url="route('appointment.show', ['locale' => app()->getLocale(), 'appointment' => $appointment])">
{{ __('site.actions.view_details') }}
</x-mail::button>

{{ __('site.footer.disclaimer') }}
</x-mail::message>
