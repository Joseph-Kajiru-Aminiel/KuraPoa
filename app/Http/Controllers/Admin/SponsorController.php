<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use App\Services\AuditLogger;
use App\Services\SponsorLogoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SponsorController extends Controller
{
    public function index(): View
    {
        return view('admin.sponsors.index', ['sponsors' => Sponsor::query()->orderBy('sort_order')->orderBy('name')->get()]);
    }

    public function store(Request $request, AuditLogger $auditLogger, SponsorLogoService $logoService): RedirectResponse
    {
        $data = $this->validated($request);
        $data['logo'] = $request->file('logo') ? $logoService->store($request->file('logo')) : null;
        $sponsor = Sponsor::query()->create($data);
        $auditLogger->record('sponsor.created', ['sponsor_id' => $sponsor->id]);

        return back()->with('success', __('Mshirika ameongezwa.'));
    }

    public function update(Request $request, Sponsor $sponsor, AuditLogger $auditLogger, SponsorLogoService $logoService): RedirectResponse
    {
        $data = $this->validated($request);
        $oldLogo = $sponsor->logo;

        if ($request->hasFile('logo')) {
            $data['logo'] = $logoService->store($request->file('logo'));
        } else {
            unset($data['logo']);
        }

        DB::transaction(function () use ($sponsor, $data, $auditLogger): void {
            $lockedSponsor = Sponsor::query()->lockForUpdate()->findOrFail($sponsor->id);
            $lockedSponsor->update($data);
            $auditLogger->record('sponsor.updated', ['sponsor_id' => $lockedSponsor->id]);
        });

        if (isset($data['logo']) && $oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        return back()->with('success', __('Taarifa za mshirika zimehifadhiwa.'));
    }

    public function destroy(Sponsor $sponsor, AuditLogger $auditLogger): RedirectResponse
    {
        $logo = DB::transaction(function () use ($sponsor, $auditLogger): ?string {
            $lockedSponsor = Sponsor::query()->lockForUpdate()->findOrFail($sponsor->id);
            $logo = $lockedSponsor->logo;
            $lockedSponsor->delete();
            $auditLogger->record('sponsor.deleted', ['sponsor_id' => $lockedSponsor->id]);

            return $logo;
        });

        if ($logo) {
            Storage::disk('public')->delete($logo);
        }

        return back()->with('success', __('Mshirika amefutwa.'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'logo' => [
                'nullable',
                'file',
                'mimetypes:image/jpeg,image/png,image/webp,image/svg+xml',
                'extensions:jpg,jpeg,png,webp,svg',
                'max:2048',
            ],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
