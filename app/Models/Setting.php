<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $guarded = [];

    public $timestamps = true;

    public const CACHE_KEY = 'settings.all';

    /**
     * All settings as a key => value map, cached until something is saved.
     *
     * A plain array is cached rather than a Collection: Laravel 13's cache
     * stores unserialize with an allowed-classes allowlist, so any object that
     * is not on it comes back as __PHP_Incomplete_Class.
     */
    public static function map(): Collection
    {
        return collect(Cache::rememberForever(
            self::CACHE_KEY,
            fn () => static::query()->pluck('value', 'key')->all()
        ));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::map()->get($key, $default);
    }

    public static function put(string $key, mixed $value, string $group = 'general', string $type = 'text'): void
    {
        static::updateOrCreate(['key' => $key], compact('value', 'group', 'type'));
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::forgetCache());
        static::deleted(fn () => self::forgetCache());
    }
}
