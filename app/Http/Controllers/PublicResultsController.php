<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Services\ResultService;
use Illuminate\View\View;

class PublicResultsController extends Controller
{
    public function show(Election $election, ResultService $resultService): View
    {
        $results = $resultService->forElection($election);

        abort_if($results === null, 404);

        return view('results.show', compact('election', 'results'));
    }
}
