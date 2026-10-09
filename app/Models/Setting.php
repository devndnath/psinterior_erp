<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Key-value settings row. Always read/write through the static helpers
 * here (or the `Setting::get()`/`Setting::set()` wrappers) rather than
 * querying the table directly, so the cache layer stays correct.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings.all'));
        static::deleted(fn () => Cache::forget('settings.all'));
    }

    public static function all_cached(): \Illuminate\Support\Collection
    {
        return Cache::rememberForever('settings.all', fn () => static::query()->get()->keyBy('key'));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all_cached()->get($key)?->value ?? $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): self
    {
        return static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
    }

    public static function group(string $group): \Illuminate\Support\Collection
    {
        return static::all_cached()->filter(fn ($setting) => $setting->group === $group);
    }
}
