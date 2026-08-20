<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Contracts\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        return view('public.faq', [
            'groups' => Faq::active()->ordered()->get()->groupBy('group'),
        ]);
    }
}
