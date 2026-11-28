<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Support\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    private const KEYS = ['youtube_url', 'instagram_url', 'whatsapp_number', 'contact_email'];

    public function edit(): View
    {
        return view('admin.settings.edit', ['settings' => SiteSetting::all()]);
    }

    public function update(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $values = $request->validate([
            'youtube_url' => ['nullable', 'url', 'max:2048', 'regex:/^https:\\/\\/.+/i'],
            'instagram_url' => ['nullable', 'url', 'max:2048', 'regex:/^https:\\/\\/.+/i'],
            'whatsapp_number' => ['nullable', 'regex:/^255[0-9]{9}$/'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ], [
            'youtube_url.regex' => __('Tumia anwani salama inayoanza na https://.'),
            'instagram_url.regex' => __('Tumia anwani salama inayoanza na https://.'),
            'whatsapp_number.regex' => __('Namba ya WhatsApp iwe na tarakimu 12 na ianze na 255.'),
        ]);

        foreach (self::KEYS as $key) {
            $values[$key] = isset($values[$key]) ? trim($values[$key]) : null;
        }

        DB::transaction(function () use ($values, $auditLogger): void {
            $now = now();
            DB::table('site_settings')->upsert(
                array_map(fn (string $key, ?string $value): array => [
                    'key' => $key,
                    'value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], array_keys($values), array_values($values)),
                ['key'],
                ['value', 'updated_at'],
            );

            $auditLogger->record('site_settings.updated', ['keys' => array_keys($values)]);
        });

        SiteSetting::clearCache();

        return back()->with('success', __('Mipangilio imehifadhiwa.'));
    }
}
