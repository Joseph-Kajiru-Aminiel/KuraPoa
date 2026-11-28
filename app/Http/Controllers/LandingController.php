<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\Institution;
use App\Models\Sponsor;
use App\Models\Voter;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __invoke(): View
    {
        $ongoingElections = Election::query()
            ->where('status', 'open')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->orderBy('ends_at')
            ->get();

        $closedElections = Election::query()
            ->where(fn ($query) => $query->where('status', 'closed')->orWhere(fn ($query) => $query
                ->where('status', 'open')
                ->where('ends_at', '<', now())))
            ->latest('ends_at')
            ->limit(4)
            ->get();

        $sponsors = Sponsor::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $stats = [
            'elections' => Election::query()
                ->where(fn ($query) => $query->where('status', 'closed')->orWhere('ends_at', '<', now()))
                ->count(),
            'votes' => Voter::query()->where('has_voted', true)->count(),
            'institutions' => Institution::query()->where('is_active', true)->count(),
        ];

        return view('welcome', compact('ongoingElections', 'closedElections', 'sponsors', 'stats'));
    }
}
