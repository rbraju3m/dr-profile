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
     * Memoised for the life of the request, the way DoctorProfile::current()
     * is, and for the same reason: this is read constantly.
     *
     * Every feature(), setting() and theme() call lands here, and the cache
     * store is the database, so without this each one was its own SELECT
     * against the cache table — ninety-five of them to draw one homepage.
     */
    protected static ?Collection $memo = null;

    /**
     * All settings as a key => value map, cached until something is saved.
     *
     * A plain array is cached rather than a Collection: Laravel 13's cache
     * stores unserialize with an allowed-classes allowlist, so any object that
     * is not on it comes back as __PHP_Incomplete_Class. The Collection is
     * re-wrapped here, after it comes back.
     */
    public static function map(): Collection
    {
        return static::$memo ??= collect(Cache::rememberForever(
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

    /** Drops both halves: the request's copy and the shared one. */
    public static function forgetCache(): void
    {
        static::$memo = null;
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::forgetCache());
        static::deleted(fn () => self::forgetCache());
    }
}
