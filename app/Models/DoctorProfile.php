<?php

namespace App\Models;

use App\Concerns\HasMedia;
use App\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

/**
 * The one and only doctor this platform is built around.
 * Read it through DoctorProfile::current() — never query the table directly.
 */
class DoctorProfile extends Model
{
    use HasMedia, HasTranslations;

    protected $table = 'doctor_profile';

    protected $guarded = [];

    protected array $translatable = [
        'name', 'title', 'designation', 'tagline', 'degrees',
        'short_bio', 'bio', 'philosophy', 'languages',
        'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
        ];
    }

    /**
     * Memoised for the life of the request rather than cached in a store:
     * it is a single row, and serialising an Eloquent model into a persistent
     * cache breaks as soon as the class definition changes.
     */
    protected static ?self $currentInstance = null;

    public static function current(): self
    {
        return static::$currentInstance ??= static::query()->firstOrNew([]);
    }

    public static function forgetCache(): void
    {
        static::$currentInstance = null;
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::forgetCache());
        static::deleted(fn () => self::forgetCache());
    }

    /** "Prof. Dr. Ayesha Rahman" — title and name joined for the active locale. */
    public function fullName(): string
    {
        return trim(($this->tr('title') ?? '').' '.($this->tr('name') ?? ''));
    }

    public function photoUrl(): ?string
    {
        return $this->mediaUrl('photo');
    }

    public function heroImageUrl(): ?string
    {
        return $this->mediaUrl('hero_image');
    }
}
