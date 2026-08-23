<?php

namespace App\Support;

use App\Models\Chamber;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * The two lists every public page carries whatever else it is showing: the
 * chambers named in the header, footer and phone bar, and the pages the admin
 * has put in the footer.
 *
 * They are built once per request rather than once per view. The composer that
 * hands them out is registered for the layout, its partials *and* public.*,
 * because slot content renders in the caller's scope — so drawing one homepage
 * fires it six or seven times, and each firing used to be two more queries.
 *
 * The memo hangs off the request rather than a static property or a scoped
 * container binding, because the request is the only one of the three that is
 * genuinely replaced for each request. Laravel releases scoped bindings in the
 * queue worker and under Octane, but not between two calls in a test — so a
 * chamber created halfway through a test would not appear on the page that
 * followed it, and the memo would be reporting the past.
 */
final class SiteNavigation
{
    private const ATTRIBUTE = 'site.navigation';

    private ?Collection $chambers = null;

    private ?Collection $footerPages = null;

    public static function forRequest(Request $request): self
    {
        if (! $request->attributes->get(self::ATTRIBUTE) instanceof self) {
            $request->attributes->set(self::ATTRIBUTE, new self);
        }

        return $request->attributes->get(self::ATTRIBUTE);
    }

    /** Active chambers, in the order the admin dragged them into. */
    public function chambers(): Collection
    {
        return $this->chambers ??= Chamber::active()->ordered()->get();
    }

    /** Published pages flagged for the footer. */
    public function footerPages(): Collection
    {
        return $this->footerPages ??= Page::published()
            ->where('show_in_footer', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
