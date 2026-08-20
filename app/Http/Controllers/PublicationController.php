<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use Illuminate\Contracts\View\View;

class PublicationController extends Controller
{
    public function index(): View
    {
        return view('public.publications', [
            'publications' => Publication::active()->newestFirst()->get()->groupBy('year'),
        ]);
    }
}
