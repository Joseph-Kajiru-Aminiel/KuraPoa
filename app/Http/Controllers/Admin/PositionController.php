<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Position;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    public function store(Request $request, Election $election, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:255']]);
        DB::transaction(function () use ($election, $data, $auditLogger): void {
            $lockedElection = Election::query()->lockForUpdate()->findOrFail($election->id);
            $this->ensureDraft($lockedElection);
            $position = $lockedElection->positions()->create($data + ['sort_order' => $lockedElection->positions()->count()]);
            $auditLogger->record('position.created', ['election_id' => $lockedElection->id, 'position_id' => $position->id]);
        });

        return back()->with('success', 'Nafasi imeongezwa.');
    }

    public function update(Request $request, Election $election, Position $position, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:255']]);
        DB::transaction(function () use ($election, $position, $data, $auditLogger): void {
            $lockedElection = Election::query()->lockForUpdate()->findOrFail($election->id);
            $this->ensureDraft($lockedElection);
            $lockedPosition = Position::query()
                ->where('election_id', $lockedElection->id)
                ->findOrFail($position->id);
            $lockedPosition->update($data);
            $auditLogger->record('position.updated', ['election_id' => $lockedElection->id, 'position_id' => $lockedPosition->id]);
        });

        return back()->with('success', 'Nafasi imesasishwa.');
    }

    public function destroy(Election $election, Position $position, AuditLogger $auditLogger): RedirectResponse
    {
        DB::transaction(function () use ($election, $position, $auditLogger): void {
            $lockedElection = Election::query()->lockForUpdate()->findOrFail($election->id);
            $this->ensureDraft($lockedElection);
            $lockedPosition = Position::query()
                ->where('election_id', $lockedElection->id)
                ->findOrFail($position->id);
            $positionId = $lockedPosition->id;
            $lockedPosition->delete();
            $auditLogger->record('position.deleted', ['election_id' => $lockedElection->id, 'position_id' => $positionId]);
        });

        return back()->with('success', 'Nafasi imefutwa.');
    }

    private function ensureDraft(Election $election): void
    {
        abort_unless($election->status === 'draft', 403);
    }
}
