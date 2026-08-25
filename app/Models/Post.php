<?php

namespace App\Models;

use App\Concerns\HasMedia;
use App\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * News, events and health-tip articles. `type` decides which listing it appears
 * in and whether the event_* columns are meaningful.
 */
class Post extends Model
{
    use HasMedia, HasTranslations;

    protected $guarded = [];

    /** @var array<int, string> */
    protected array $mediaColumns = ['image'];

    public const TYPES = ['news', 'event', 'blog'];

    protected array $translatable = [
        'title', 'excerpt', 'content', 'event_venue',
        'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'event_start_at' => 'datetime',
            'event_end_at' => 'datetime',
            'event_is_online' => 'boolean',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'views' => 'integer',
            'reading_minutes' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', Carbon::now()));
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeNews(Builder $query): Builder
    {
        return $query->ofType('news');
    }

    public function scopeEvents(Builder $query): Builder
    {
        return $query->ofType('event');
    }

    public function scopeBlog(Builder $query): Builder
    {
        return $query->ofType('blog');
    }

    /**
     * Anything dragged into place in the admin leads; everything else falls
     * back to newest first. sort_order defaults to 0, so a list nobody has
     * reordered behaves exactly as it did before.
     */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('published_at')->orderByDesc('id');
    }

    /**
     * Events sort by when they happen, upcoming first.
     *
     * An event is over when it *ends*, not when it starts. Splitting the list
     * on the start alone filed a three-day conference under "Past Events" on
     * its opening morning, and closed its registration button with two days
     * still to run. Where no end was given the start is the end.
     */
    public function scopeUpcomingEvents(Builder $query): Builder
    {
        return $query->events()
            ->whereRaw('COALESCE(event_end_at, event_start_at) >= ?', [Carbon::now()])
            ->orderBy('event_start_at');
    }

    public function scopePastEvents(Builder $query): Builder
    {
        return $query->events()
            ->whereRaw('COALESCE(event_end_at, event_start_at) < ?', [Carbon::now()])
            ->orderByDesc('event_start_at');
    }

    /** True while the event is still to come *or* still running. */
    public function isUpcoming(): bool
    {
        $ends = $this->event_end_at ?? $this->event_start_at;

        return $ends !== null && $ends->isFuture();
    }

    /** True only once it has begun and not yet finished. */
    public function isInProgress(): bool
    {
        return $this->event_start_at !== null
            && ! $this->event_start_at->isFuture()
            && $this->isUpcoming();
    }

    public function imageUrl(): ?string
    {
        return $this->mediaUrl('image');
    }
}
