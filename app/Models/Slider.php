<?php

namespace App\Models;

use App\Concerns\HasMedia;
use App\Concerns\HasTranslations;
use App\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasMedia, HasTranslations, Sortable;

    protected $guarded = [];

    /** @var array<int, string> */
    protected array $mediaColumns = ['image', 'mobile_image'];

    protected array $translatable = ['title', 'subtitle', 'cta_label'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function imageUrl(): ?string
    {
        return $this->mediaUrl('image');
    }

    public function mobileImageUrl(): ?string
    {
        return $this->mediaUrl('mobile_image') ?? $this->imageUrl();
    }
}
