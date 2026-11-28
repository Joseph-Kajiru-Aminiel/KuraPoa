<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdministrator() || $user->isObserver();
    }

    public function view(User $user, Complaint $complaint): bool
    {
        return $this->canAccessInstitution($user, $complaint->institution_id);
    }

    public function create(User $user): bool
    {
        return $user->isAdministrator() && $user->institution_id !== null;
    }

    public function update(User $user, Complaint $complaint): bool
    {
        return $user->isAdministrator() && $this->canAccessInstitution($user, $complaint->institution_id);
    }

    private function canAccessInstitution(User $user, int $institutionId): bool
    {
        return $user->is_super_admin
            || (($user->isAdministrator() || $user->isObserver()) && $user->institution_id === $institutionId);
    }
}
