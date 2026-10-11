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
        return Cache::rememberForever('settings.all', fn () => static::query()->get()->keyBy('key'))
            ->each(fn (self $setting) => static::scrubUtf8($setting));
    }

    /**
     * Cache::rememberForever() stores this collection as a serialized
     * blob and, on every cache hit, restores it with a raw unserialize()
     * — which never fires Eloquent's `retrieved` event. That makes this
     * the one path in the app where AppServiceProvider's global
     * Model::retrieved() scrub (added for the invalid-UTF-8 500 on
     * Livewire's JSON response) can never run, so a setting cached with
     * a bad byte before either fix existed would keep crashing forever,
     * cache entry or not. Scrubbed explicitly here instead, on every
     * call, so it self-heals regardless of how old the cached blob is.
     */
    private static function scrubUtf8(self $setting): void
    {
        $attributes = $setting->getAttributes();
        $fixed = null;

        foreach ($attributes as $key => $value) {
            if (is_string($value) && $value !== '' && ! mb_check_encoding($value, 'UTF-8')) {
                $fixed ??= $attributes;
                $fixed[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            }
        }

        if ($fixed !== null) {
            $setting->setRawAttributes($fixed, true);
        }
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
