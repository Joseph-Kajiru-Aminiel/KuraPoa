<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use JsonException;

class AuditLogger
{
    /**
     * Store administration metadata only; never pass voter choices or access codes.
     *
     * @throws JsonException
     */
    public function record(string $action, array $details = [], ?User $user = null): void
    {
        AuditLog::query()->create([
            'user_id' => $user?->id ?? Auth::id(),
            'action' => $action,
            'details' => $details === [] ? null : json_encode($details, JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }
}
