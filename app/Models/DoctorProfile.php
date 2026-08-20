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

    /**
     * Every social account that has actually been filled in.
     *
     * Header and footer used to keep their own hardcoded lists, which is how
     * Instagram ended up editable but missing from the header and X editable
     * and shown nowhere at all. One list now feeds both.
     *
     * @return array<int, array{network: string, url: string, label: string}>
     */
    public function socialLinks(): array
    {
        $networks = [
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
            'linkedin' => 'LinkedIn',
            'x' => 'X',
        ];

        $links = [];

        foreach ($networks as $network => $label) {
            $url = $this->getAttributeValue($network.'_url');

            if (filled($url)) {
                $links[] = ['network' => $network, 'url' => $url, 'label' => $label];
            }
        }

        return $links;
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
