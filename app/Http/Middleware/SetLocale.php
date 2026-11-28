<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale')
            ?? ($request->user()?->is_admin ? $request->user()->locale : null)
            ?? config('app.locale', 'sw');

        if (! in_array($locale, ['sw', 'en'], true)) {
            $locale = 'sw';
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        if (class_exists(\Locale::class)) {
            \Locale::setDefault($locale === 'sw' ? 'sw_TZ' : 'en_TZ');
        }

        return $next($request);
    }
}
