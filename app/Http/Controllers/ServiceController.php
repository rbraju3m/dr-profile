<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\SuccessStory;
use App\Models\Testimonial;
use Illuminate\Contracts\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('public.services.index', [
            'services' => Service::active()->ordered()->get(),
        ]);
    }

    public function show(Service $service): View
    {
        abort_unless($service->is_active, 404);

        return view('public.services.show', [
            'service' => $service,
            'related' => Service::active()->ordered()->whereKeyNot($service->id)->take(4)->get(),
            'stories' => SuccessStory::published()->where('service_id', $service->id)->latestFirst()->take(3)->get(),
            'testimonials' => Testimonial::published()->where('service_id', $service->id)->ordered()->take(3)->get(),
        ]);
    }
}
