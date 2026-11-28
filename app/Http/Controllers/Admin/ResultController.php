<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Services\ResultService;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(): View
    {
        $elections = Election::query()
            ->withCount(['voters', 'voters as voted_count' => fn ($query) => $query->where('has_voted', true)])
            ->latest()
            ->paginate(10);

        return view('admin.results.index', compact('elections'));
    }

    public function show(Election $election, ResultService $resultService): View
    {
        $results = $resultService->forElection($election);
        $turnout = $results['turnout'] ?? [
            'registered' => $election->voters()->count(),
            'voted' => $election->voters()->where('has_voted', true)->count(),
            'percentage' => 0,
        ];

        if ($results === null && $turnout['registered'] > 0) {
            $turnout['percentage'] = round(($turnout['voted'] / $turnout['registered']) * 100, 1);
        }

        return view('admin.results.show', compact('election', 'results', 'turnout'));
    }
}
