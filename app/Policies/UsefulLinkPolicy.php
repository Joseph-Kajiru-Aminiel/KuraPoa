<?php

namespace App\Policies;

use App\Models\User;

class UsefulLinkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_super_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_super_admin;
    }

    public function update(User $user): bool
    {
        return $user->is_super_admin;
    }

    public function delete(User $user): bool
    {
        return $user->is_super_admin;
    }
}
