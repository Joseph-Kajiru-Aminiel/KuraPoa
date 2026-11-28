<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InstitutionController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.institution.edit', ['institution' => $this->institution($request)]);
    }

    public function update(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $institution = $this->institution($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:institutions,code,'.$institution->id],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'reg_number_pattern' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);
        if (isset($data['reg_number_pattern'])) {
            set_error_handler(static fn (): bool => true, E_WARNING);
            try {
                $validPattern = preg_match($data['reg_number_pattern'], '') !== false;
            } finally {
                restore_error_handler();
            }

            if (! $validPattern) {
                throw ValidationException::withMessages([
                    'reg_number_pattern' => __('Muundo wa namba za usajili si regex halali.'),
                ]);
            }
        }
        $oldLogo = $institution->logo;

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('institutions', 'public');
        } else {
            unset($data['logo']);
        }

        DB::transaction(function () use ($institution, $data, $auditLogger): void {
            $lockedInstitution = Institution::query()->lockForUpdate()->findOrFail($institution->id);
            $lockedInstitution->update($data);
            $auditLogger->record('institution.updated', ['institution_id' => $lockedInstitution->id]);
        });

        if (isset($data['logo']) && $oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        return back()->with('success', __('Taarifa za taasisi zimehifadhiwa.'));
    }

    private function institution(Request $request): Institution
    {
        $user = $request->user();
        $institutionId = $user->is_super_admin
            ? $request->integer('institution_id') ?: $user->institution_id
            : $user->institution_id;

        abort_if(! $institutionId, 404);

        return Institution::query()->findOrFail($institutionId);
    }
}
