<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class SiteSetting
{
    private const CACHE_KEY = 'kurapoa.site-settings';

    public static function get(string $key): ?string
    {
        return self::all()[$key] ?? null;
    }

    /**
     * @return array<string, string|null>
     */
    public static function all(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn (): array => DB::table('site_settings')->pluck('value', 'key')->all(),
        );
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
