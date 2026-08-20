<?php

namespace App\Concerns;

/**
 * Bilingual content lives in paired columns: `name_en` / `name_bn`.
 *
 * Declare the base names in $translatable and the model exposes them as plain
 * attributes resolved against the active locale, falling back to English when
 * the Bangla value has not been filled in yet:
 *
 *     $service->name        // active locale, English fallback
 *     $service->tr('name', 'bn')  // explicit locale
 */
trait HasTranslations
{
    public function tr(string $field, ?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $value = $this->getAttributeValue("{$field}_{$locale}");

        if (blank($value)) {
            $fallback = config('app.fallback_locale', 'en');
            $value = $locale === $fallback ? null : $this->getAttributeValue("{$field}_{$fallback}");
        }

        return $value;
    }

    public function getAttribute($key)
    {
        if (! array_key_exists($key, $this->attributes) && $this->isTranslatable($key)) {
            return $this->tr($key);
        }

        return parent::getAttribute($key);
    }

    public function isTranslatable(string $key): bool
    {
        return in_array($key, $this->translatable ?? [], true);
    }

    /** Base names of every translatable field on this model. */
    public function translatableFields(): array
    {
        return $this->translatable ?? [];
    }
}
