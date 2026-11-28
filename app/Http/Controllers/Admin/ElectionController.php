<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ElectionController extends Controller
{
    public function index(): View
    {
        $elections = Election::query()
            ->withCount(['positions', 'voters', 'voters as voted_count' => fn ($query) => $query->where('has_voted', true)])
            ->latest()
            ->paginate(10);

        return view('admin.elections.index', compact('elections'));
    }

    public function create(): View
    {
        return view('admin.elections.create');
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $election = DB::transaction(function () use ($data, $auditLogger): Election {
            $election = Election::create($data + ['status' => 'draft']);
            $auditLogger->record('election.created', ['election_id' => $election->id]);

            return $election;
        });

        return redirect()->route('admin.elections.show', $election)->with('success', 'Uchaguzi umeundwa.');
    }

    public function show(Request $request, Election $election): View
    {
        $election->load(['positions.candidates']);
        $registered = $election->voters()->count();
        $voted = $election->voters()->where('has_voted', true)->count();
        $search = trim((string) $request->query('q', ''));
        $voters = $election->voters()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('reg_number', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            }))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.elections.show', compact('election', 'voters', 'search', 'registered', 'voted'));
    }

    public function edit(Election $election): View
    {
        abort_unless($election->status === 'draft', 403);

        return view('admin.elections.edit', compact('election'));
    }

    public function update(Request $request, Election $election, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        DB::transaction(function () use ($election, $data, $auditLogger): void {
            $lockedElection = Election::query()->lockForUpdate()->findOrFail($election->id);
            abort_unless($lockedElection->status === 'draft', 403);
            $lockedElection->update($data);
            $auditLogger->record('election.updated', ['election_id' => $lockedElection->id]);
        });

        return redirect()->route('admin.elections.show', $election)->with('success', 'Taarifa za uchaguzi zimehifadhiwa.');
    }

    public function destroy(Election $election, AuditLogger $auditLogger): RedirectResponse
    {
        DB::transaction(function () use ($election, $auditLogger): void {
            $lockedElection = Election::query()->lockForUpdate()->findOrFail($election->id);
            abort_unless($lockedElection->status === 'draft', 403);
            $electionId = $lockedElection->id;
            $lockedElection->delete();
            $auditLogger->record('election.deleted', ['election_id' => $electionId]);
        });

        return redirect()->route('admin.elections.index')->with('success', 'Uchaguzi umefutwa.');
    }
}
