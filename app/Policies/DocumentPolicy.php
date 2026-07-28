<?php

namespace App\Policies;

use App\Enums\DocumentState;
use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Document $document): bool
    {
        return $user->id === $document->user_id
            || $user->hasRole(['Legal_Compliance', 'Manager']);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Document $document): bool
    {
        if ($document->state === DocumentState::EXECUTED) {
            return false;
        }

        return $user->id === $document->user_id
            || $user->hasRole(['Legal_Compliance', 'Manager']);
    }

    public function delete(User $user, Document $document): bool
    {
        if ($document->state === DocumentState::EXECUTED) {
            return false;
        }

        return $user->id === $document->user_id
            || $user->hasRole('Manager');
    }
}
