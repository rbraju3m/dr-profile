<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            // Honeypot: real people leave it empty.
            'website' => ['nullable', 'size:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('site.contact.name'),
            'email' => __('site.contact.email'),
            'phone' => __('site.contact.phone'),
            'subject' => __('site.contact.subject'),
            'message' => __('site.contact.message'),
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => __('validation_custom.phone'),
            'website.size' => __('validation_custom.spam'),
        ];
    }
}
