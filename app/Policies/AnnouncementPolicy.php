<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewInstitution($user, $user->institution_id);
    }

    public function view(User $user, Announcement $announcement): bool
    {
        return $this->canViewInstitution($user, $announcement->institution_id);
    }

    public function create(User $user): bool
    {
        return $user->isAdministrator() && $user->institution_id !== null;
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $this->canManageInstitution($user, $announcement->institution_id);
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $this->update($user, $announcement);
    }

    private function canViewInstitution(User $user, ?int $institutionId): bool
    {
        return $user->is_super_admin || ($institutionId !== null && $user->institution_id === $institutionId);
    }

    private function canManageInstitution(User $user, int $institutionId): bool
    {
        return $user->isAdministrator()
            && ($user->is_super_admin || $user->institution_id === $institutionId);
    }
}
