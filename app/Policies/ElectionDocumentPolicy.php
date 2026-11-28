<?php

namespace App\Policies;

use App\Models\ElectionDocument;
use App\Models\User;

class ElectionDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdministrator() || $user->isObserver();
    }

    public function view(User $user, ElectionDocument $document): bool
    {
        return $this->canAccess($user, $document);
    }

    public function download(User $user, ElectionDocument $document): bool
    {
        return $this->canAccess($user, $document);
    }

    public function create(User $user): bool
    {
        return $user->isAdministrator() && $user->institution_id !== null;
    }

    public function update(User $user, ElectionDocument $document): bool
    {
        return $user->isAdministrator()
            && ($user->is_super_admin || $document->election->institution_id === $user->institution_id);
    }

    public function delete(User $user, ElectionDocument $document): bool
    {
        return $this->update($user, $document);
    }

    private function canAccess(User $user, ElectionDocument $document): bool
    {
        if ($user->isAdministrator()) {
            return $user->is_super_admin || $document->election->institution_id === $user->institution_id;
        }

        return $user->isObserver()
            && $user->institution_id === $document->election->institution_id
            && $user->assignedElections()->whereKey($document->election_id)->exists();
    }
}
