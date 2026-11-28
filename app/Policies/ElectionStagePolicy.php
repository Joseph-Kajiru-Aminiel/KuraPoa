<?php

namespace App\Policies;

use App\Models\ElectionStage;
use App\Models\User;

class ElectionStagePolicy
{
    public function update(User $user, ElectionStage $stage): bool
    {
        return $stage->key !== 'voting'
            && $user->isAdministrator()
            && ($user->is_super_admin || $stage->election->institution_id === $user->institution_id);
    }
}
