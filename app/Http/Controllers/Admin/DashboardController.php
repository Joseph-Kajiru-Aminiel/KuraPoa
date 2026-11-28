<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Voter;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $elections = Election::query()
            ->withCount(['positions', 'voters', 'voters as voted_count' => fn ($query) => $query->where('has_voted', true)])
            ->latest()
            ->limit(6)
            ->get();

        return view('admin.dashboard', [
            'elections' => $elections,
            'stats' => [
                'elections' => Election::count(),
                'candidates' => Candidate::count(),
                'voters' => Voter::count(),
                'voted' => Voter::where('has_voted', true)->count(),
            ],
        ]);
    }
}
