<?php

namespace App\Models;

use App\Concerns\HasMedia;
use App\Concerns\HasTranslations;
use App\Concerns\Sortable;
use App\Support\MapEmbed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chamber extends Model
{
    use HasMedia, HasTranslations, Sortable;

    protected $guarded = [];

    protected array $translatable = ['name', 'address', 'city', 'note'];

    protected function casts(): array
    {
        return [
            'consultation_fee' => 'decimal:2',
            'followup_fee' => 'decimal:2',
            'accepts_online_booking' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ChamberSchedule::class)->orderBy('day_of_week')->orderBy('start_time');
    }

    public function activeSchedules(): HasMany
    {
        return $this->schedules()->where('is_active', true);
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ScheduleException::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function imageUrl(): ?string
    {
        return $this->mediaUrl('image');
    }

    /**
     * The map as an address the view can frame — null when what was pasted is
     * not a map. What the operator typed is never printed as markup; see
     * App\Support\MapEmbed for why.
     */
    public function mapEmbedUrl(): ?string
    {
        return MapEmbed::url($this->map_embed);
    }
}
