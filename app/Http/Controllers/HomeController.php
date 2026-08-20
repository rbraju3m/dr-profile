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
use App\Support\Features;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class HomeController extends Controller
{
    /**
     * Every band is fetched only while its switch is on. An empty collection
     * for a band that is off is what the view already knows how to render —
     * nothing — and it saves the queries behind it, including the slot search
     * that finds each chamber's next free date.
     */
    public function index(SlotService $slots): View
    {
        $chambers = $this->when('home_chambers', fn () => Chamber::with('activeSchedules')->active()->ordered()->get());

        return view('public.home', [
            'sliders' => $this->when('home_hero', fn () => Slider::active()->ordered()->get()),
            'stats' => $this->when('home_stats', fn () => Stat::active()->ordered()->get()),
            'services' => $this->when('home_services', fn () => Service::active()->where('is_featured', true)->ordered()->take(6)->get()),
            'chambers' => $chambers,
            'nextDates' => $chambers->mapWithKeys(
                fn (Chamber $chamber) => [$chamber->id => $slots->nextAvailableDate($chamber)]
            ),
            'stories' => $this->when('home_stories', fn () => SuccessStory::published()->where('is_featured', true)->latestFirst()->take(3)->get()),
            'testimonials' => $this->when('home_testimonials', fn () => Testimonial::published()->ordered()->take(6)->get()),
            'news' => $this->when('home_news', fn () => Post::published()->whereIn('type', $this->postTypes())->latestFirst()->take(3)->get()),
            'articles' => $this->when('home_blog', fn () => Post::published()->blog()->latestFirst()->take(3)->get()),
            'faqs' => $this->when('home_faq', fn () => Faq::active()->ordered()->take(6)->get()),
        ]);
    }

    /** @return Collection<int, Model> */
    private function when(string $feature, callable $query): Collection
    {
        return Features::enabled($feature) ? $query() : new Collection;
    }

    /**
     * One band carries both news and events, and they are separate switches —
     * with events off the band must stop listing them rather than offering
     * cards whose pages have closed.
     *
     * @return list<string>
     */
    private function postTypes(): array
    {
        return array_values(array_filter([
            Features::enabled('news') ? 'news' : null,
            Features::enabled('events') ? 'event' : null,
        ]));
    }
}
