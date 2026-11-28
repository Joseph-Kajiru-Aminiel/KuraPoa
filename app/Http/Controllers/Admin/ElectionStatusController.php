<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ElectionStatusController extends Controller
{
    public function open(Election $election, AuditLogger $auditLogger): RedirectResponse
    {
        DB::transaction(function () use ($election, $auditLogger): void {
            $lockedElection = Election::query()->lockForUpdate()->findOrFail($election->id);

            if ($lockedElection->status !== 'draft') {
                abort(403);
            }

            $positions = $lockedElection->positions()
                ->withCount(['candidates as approved_candidates_count' => fn ($query) => $query->where('status', 'approved')])
                ->get();

            $invalidPosition = $positions->first(fn ($position) => $position->approved_candidates_count < 2);

            if ($positions->isEmpty() || $invalidPosition) {
                throw ValidationException::withMessages([
                    'election' => $positions->isEmpty()
                        ? __('Ongeza angalau nafasi moja kabla ya kufungua uchaguzi.')
                        : __('Nafasi :position inahitaji angalau wagombea wawili walioidhinishwa.', ['position' => $invalidPosition->title]),
                ]);
            }

            if ($lockedElection->ends_at->isPast()) {
                throw ValidationException::withMessages(['election' => __('Muda wa mwisho wa uchaguzi umepita. Sasisha tarehe kwanza.')]);
            }

            $status = $lockedElection->starts_at->isFuture() ? 'scheduled' : 'open';
            $lockedElection->update(['status' => $status]);
            $auditLogger->record($status === 'scheduled' ? 'election.scheduled' : 'election.opened', ['election_id' => $lockedElection->id]);
        });

        return back()->with('success', __('Uchaguzi umefunguliwa.'));
    }

    public function close(Election $election, AuditLogger $auditLogger): RedirectResponse
    {
        DB::transaction(function () use ($election, $auditLogger): void {
            $lockedElection = Election::query()->lockForUpdate()->findOrFail($election->id);
            abort_unless(in_array($lockedElection->status, ['scheduled', 'open'], true), 403);
            $lockedElection->update(['status' => 'closed']);
            $auditLogger->record('election.closed', ['election_id' => $lockedElection->id]);
        });

        return back()->with('success', __('Uchaguzi umefungwa. Matokeo sasa yanaonekana.'));
    }
}
