<?php

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        $all = Cache::rememberForever('settings:all', function () {
            return SystemSetting::query()->pluck('value', 'key')->toArray();
        });

        return $all[$key] ?? $default;
    }
}

if (! function_exists('setting_set')) {
    function setting_set(string $key, $value, string $type = 'string'): void
    {
        SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );

        Cache::forget('settings:all'); // invalidación determinística [web:45]
    }
}
