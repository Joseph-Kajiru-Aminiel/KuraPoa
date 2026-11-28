<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function update(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, ['sw', 'en'], true), 404);
        $request->session()->put('locale', $locale);

        if ($request->user()?->is_admin) {
            $request->user()->forceFill(['locale' => $locale])->save();
        }

        $returnPath = (string) $request->input('return_path', '/');
        abort_unless(str_starts_with($returnPath, '/') && ! str_starts_with($returnPath, '//'), 422);

        return redirect(url($returnPath));
    }
}
