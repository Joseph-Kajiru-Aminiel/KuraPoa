<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\Election;
use App\Models\Position;
use App\Services\AuditLogger;
use App\Services\CandidatePhotoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CandidateController extends Controller
{
    public function index(Request $request): View
    {
        $candidates = Candidate::query()
            ->with(['position.election.institution', 'department'])
            ->when($request->filled('election_id'), fn (Builder $query) => $query->whereHas('position', fn ($q) => $q->where('election_id', $request->integer('election_id'))))
            ->when($request->filled('position_id'), fn (Builder $query) => $query->where('position_id', $request->integer('position_id')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
            ->when($request->filled('q'), fn (Builder $query) => $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%')->orWhere('reg_number', 'like', '%'.$request->string('q').'%')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.candidates.index', [
            'candidates' => $candidates,
            'elections' => Election::query()->with('positions')->latest()->get(),
        ]);
    }

    public function show(Candidate $candidate): View
    {
        $history = AuditLog::query()
            ->where('action', 'like', 'candidate.%')
            ->where(function ($query) use ($candidate): void {
                $query->where('details', 'like', '%"candidate_id":'.$candidate->id.',%')
                    ->orWhere('details', 'like', '%"candidate_id":'.$candidate->id.'}%');
            })
            ->latest('created_at')
            ->limit(100)
            ->get();

        return view('admin.candidates.show', compact('candidate', 'history'));
    }

    public function store(Request $request, Election $election, Position $position, AuditLogger $auditLogger, CandidatePhotoService $photoService): RedirectResponse
    {
        $data = $this->validated($request);
        $this->ensureDraftAndBelongs($election, $position->id);
        $this->assertRegNumber($election, $data['reg_number']);

        if ($election->positions()->whereHas('candidates', fn ($query) => $query->where('reg_number', $data['reg_number']))->exists()) {
            throw ValidationException::withMessages(['reg_number' => __('Mgombea huyu tayari amesajiliwa katika uchaguzi huu.')]);
        }

        $this->assertDepartment($election, $data['department_id'] ?? null);
        $photos = $request->file('photo') ? $photoService->store($request->file('photo')) : [
            'photo' => null,
            'photo_thumbnail' => null,
        ];
        unset($data['photo']);
        $candidate = $position->candidates()->create($data + $photos + ['status' => 'pending']);
        $auditLogger->record('candidate.created', [
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => $candidate->id,
        ]);

        $hasUnverifiedVoter = $election->voters()
            ->where('reg_number', $data['reg_number'])
            ->whereNull('verified_at')
            ->exists();

        return back()->with('success', __('Mgombea amesajiliwa na anasubiri idhini.'))
            ->with('candidate_warning', $hasUnverifiedVoter ? __('Mpiga kura mwenye namba hii hajathibitishwa.') : null);
    }

    public function update(Request $request, Election $election, Position $position, Candidate $candidate, AuditLogger $auditLogger, CandidatePhotoService $photoService): RedirectResponse
    {
        $request->validate(['remove_photo' => ['sometimes', 'boolean']]);
        $data = $this->validated($request, $candidate);
        $this->ensureDraftAndBelongs($election, $position->id);
        abort_unless($candidate->position_id === $position->id, 404);
        $this->assertRegNumber($election, $data['reg_number']);
        $this->assertDepartment($election, $data['department_id'] ?? null);
        if ($election->positions()->whereHas('candidates', fn ($query) => $query->where('reg_number', $data['reg_number'])->where('id', '<>', $candidate->id))->exists()) {
            throw ValidationException::withMessages(['reg_number' => __('Mgombea huyu tayari amesajiliwa katika uchaguzi huu.')]);
        }
        $oldPhoto = $candidate->photo;
        $oldPhotoThumbnail = $candidate->photo_thumbnail;

        if ($request->hasFile('photo')) {
            $data = array_merge($data, $photoService->store($request->file('photo')));
        } elseif ($request->boolean('remove_photo')) {
            $data['photo'] = null;
            $data['photo_thumbnail'] = null;
        } else {
            unset($data['photo']);
        }

        $candidate->update($data);
        $auditLogger->record('candidate.updated', [
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => $candidate->id,
        ]);

        if (array_key_exists('photo', $data)) {
            Storage::disk('public')->delete(array_filter([$oldPhoto, $oldPhotoThumbnail]));
        }

        return back()->with('success', __('Taarifa za mgombea zimesasishwa.'));
    }

    public function approve(Candidate $candidate, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($candidate->position->election->status === 'draft', 403);
        $candidate->update([
            'status' => 'approved',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
            'rejection_reason' => null,
        ]);
        $auditLogger->record('candidate.approved', [
            'election_id' => $candidate->position->election_id,
            'position_id' => $candidate->position_id,
            'candidate_id' => $candidate->id,
        ]);

        return back()->with('success', __('Mgombea ameidhinishwa.'));
    }

    public function reject(Request $request, Candidate $candidate, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($candidate->position->election->status === 'draft', 403);
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:500']]);
        $candidate->update([
            'status' => 'rejected',
            'rejection_reason' => $data['rejection_reason'],
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);
        $auditLogger->record('candidate.rejected', [
            'election_id' => $candidate->position->election_id,
            'position_id' => $candidate->position_id,
            'candidate_id' => $candidate->id,
            'reason' => $data['rejection_reason'],
        ]);

        return back()->with('success', __('Mgombea amekataliwa.'));
    }

    public function withdraw(Candidate $candidate, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($candidate->position->election->status === 'draft', 403);
        $candidate->update(['status' => 'withdrawn']);
        $auditLogger->record('candidate.withdrawn', [
            'election_id' => $candidate->position->election_id,
            'position_id' => $candidate->position_id,
            'candidate_id' => $candidate->id,
        ]);

        return back()->with('success', __('Mgombea amejiondoa.'));
    }

    public function destroy(Election $election, Position $position, Candidate $candidate, AuditLogger $auditLogger): RedirectResponse
    {
        $this->ensureDraftAndBelongs($election, $position->id);
        abort_unless($candidate->position_id === $position->id, 404);
        abort_if($candidate->votes()->exists(), 403);
        $candidateId = $candidate->id;
        $photos = array_filter([$candidate->photo, $candidate->photo_thumbnail]);
        $candidate->delete();
        $auditLogger->record('candidate.deleted', [
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => $candidateId,
        ]);

        if ($photos !== []) {
            Storage::disk('public')->delete($photos);
        }

        return back()->with('success', __('Mgombea amefutwa.'));
    }

    private function validated(Request $request, ?Candidate $candidate = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'reg_number' => ['required', 'string', 'max:100'],
            'department_id' => ['nullable', 'integer'],
            'manifesto' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072', 'dimensions:max_width=4000,max_height=4000'],
        ]);
    }

    private function ensureDraftAndBelongs(Election $election, int $positionId): void
    {
        abort_unless($election->status === 'draft', 403);
        abort_unless($election->positions()->whereKey($positionId)->exists(), 404);
    }

    private function assertRegNumber(Election $election, string $regNumber): void
    {
        $pattern = $election->institution?->reg_number_pattern;

        if ($pattern && preg_match($pattern, $regNumber) !== 1) {
            throw ValidationException::withMessages([
                'reg_number' => __('Namba ya usajili haifuati muundo wa taasisi.'),
            ]);
        }
    }

    private function assertDepartment(Election $election, ?int $departmentId): void
    {
        if (! $departmentId) {
            return;
        }

        abort_unless($election->institution_id && Department::query()
            ->whereKey($departmentId)
            ->whereHas('faculty', fn ($query) => $query->where('institution_id', $election->institution_id))
            ->exists(), 404);
    }
}
