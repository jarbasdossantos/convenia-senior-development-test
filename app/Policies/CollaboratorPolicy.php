<?php

namespace App\Policies;

use App\Models\Collaborator;
use App\Models\User;

class CollaboratorPolicy
{
    public function view(User $user, Collaborator $collaborator): bool
    {
        return $collaborator->user_id === $user->id;
    }

    public function update(User $user, Collaborator $collaborator): bool
    {
        return $collaborator->user_id === $user->id;
    }

    public function delete(User $user, Collaborator $collaborator): bool
    {
        return $collaborator->user_id === $user->id;
    }
}
