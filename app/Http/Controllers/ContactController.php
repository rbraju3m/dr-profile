<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\Chamber;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('public.contact', [
            'chambers' => Chamber::active()->ordered()->get(),
        ]);
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        ContactMessage::create(
            $request->safe()->except('website') + ['ip_address' => $request->ip()]
        );

        return redirect()
            ->route('contact.create')
            ->with('success', __('site.contact.success'));
    }
}
