<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'group',
        'value',
        'type',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public function getTypedValueAttribute()
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json' => json_decode($this->value, true),
            'array' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    public static function get(string $key, $default = null)
    {
        $cacheKey = "site_setting_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->typed_value : $default;
        });
    }

    public static function set(string $key, $value, string $group = 'general', string $type = 'string'): void
    {
        if (is_array($value)) {
            $value = json_encode($value);
            $type = 'json';
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type]
        );

        Cache::forget("site_setting_{$key}");
    }

    public static function getByGroup(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->mapWithKeys(fn($setting) => [$setting->key => $setting->typed_value])
            ->toArray();
    }

    public static function getPublicSettings(): array
    {
        return Cache::remember('public_site_settings', 3600, function () {
            return static::where('is_public', true)
                ->get()
                ->mapWithKeys(fn($setting) => [$setting->key => $setting->typed_value])
                ->toArray();
        });
    }
}
