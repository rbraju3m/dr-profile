<?php

namespace App\Models;

use App\Concerns\HasMedia;
use App\Concerns\HasTranslations;
use App\Support\VideoEmbed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class SuccessStory extends Model
{
    use HasMedia, HasTranslations;

    protected $guarded = [];

    /** @var array<int, string> */
    protected array $mediaColumns = ['image'];

    protected array $translatable = [
        'title', 'patient_name', 'patient_location', 'condition', 'summary', 'content',
        'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'treatment_date' => 'date',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'patient_age' => 'integer',
            'views' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', Carbon::now()));
    }

    /** A hand-made order leads; the rest stay newest first. */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('published_at')->orderByDesc('id');
    }

    /** A framable address for whatever video link was pasted in. */
    public function embedUrl(): ?string
    {
        return VideoEmbed::url($this->video_url);
    }

    public function imageUrl(): ?string
    {
        return $this->mediaUrl('image');
    }
}
