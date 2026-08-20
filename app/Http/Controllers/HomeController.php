<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use App\Models\Faq;
use App\Models\Post;
use App\Models\Service;
use App\Models\Slider;
use App\Models\Stat;
use App\Models\SuccessStory;
use App\Models\Testimonial;
use App\Services\SlotService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(SlotService $slots): View
    {
        $chambers = Chamber::with('activeSchedules')->active()->ordered()->get();

        return view('public.home', [
            'sliders' => Slider::active()->ordered()->get(),
            'stats' => Stat::active()->ordered()->get(),
            'services' => Service::active()->where('is_featured', true)->ordered()->take(6)->get(),
            'chambers' => $chambers,
            'nextDates' => $chambers->mapWithKeys(
                fn (Chamber $chamber) => [$chamber->id => $slots->nextAvailableDate($chamber)]
            ),
            'stories' => SuccessStory::published()->where('is_featured', true)->latestFirst()->take(3)->get(),
            'testimonials' => Testimonial::published()->ordered()->take(6)->get(),
            'news' => Post::published()->whereIn('type', ['news', 'event'])->latestFirst()->take(3)->get(),
            'upcomingEvents' => Post::published()->upcomingEvents()->take(3)->get(),
            'articles' => Post::published()->blog()->latestFirst()->take(3)->get(),
            'faqs' => Faq::active()->ordered()->take(6)->get(),
        ]);
    }
}
