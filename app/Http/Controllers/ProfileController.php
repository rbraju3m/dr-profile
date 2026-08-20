<?php

namespace App\Http\Controllers;

use App\Models\Credential;
use App\Models\Publication;
use App\Models\Service;
use App\Models\Stat;
use Illuminate\Contracts\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $credentials = Credential::active()->ordered()->get()->groupBy('type');

        return view('public.about', [
            'credentials' => $credentials,
            'stats' => Stat::active()->ordered()->get(),
            'services' => Service::active()->ordered()->get(),
            'publications' => Publication::active()->newestFirst()->take(5)->get(),
        ]);
    }
}
