<?php

namespace App\Observers;

use App\Enums\DocumentState;
use App\Exceptions\InvalidStateTransitionException;
use App\Models\Document;
use App\Models\DocumentHistory;
use Illuminate\Support\Facades\Auth;

class DocumentObserver
{
    public function updating(Document $document): void
    {
        $originalStateRaw = $document->getOriginal('state');
        $originalState = $originalStateRaw instanceof DocumentState
            ? $originalStateRaw
            : DocumentState::from($originalStateRaw);

        if ($originalState === DocumentState::EXECUTED) {
            throw new InvalidStateTransitionException();
        }

        if ($document->isDirty('state')) {
            $newStateRaw = $document->state;
            $newState = $newStateRaw instanceof DocumentState
                ? $newStateRaw
                : DocumentState::from($newStateRaw);

            if (! $originalState->canTransitionTo($newState)) {
                throw new InvalidStateTransitionException();
            }

            $requiredRole = $originalState->requiredRoleForTransition();
            $user = Auth::user();

            if ($requiredRole !== null && ($user === null || ! $user->hasRole($requiredRole))) {
                throw new InvalidStateTransitionException();
            }
        }
    }

    public function updated(Document $document): void
    {
        if ($document->wasChanged('state')) {
            $originalStateRaw = $document->getOriginal('state');
            $originalStateValue = $originalStateRaw instanceof DocumentState
                ? $originalStateRaw->value
                : $originalStateRaw;

            $newStateRaw = $document->state;
            $newStateValue = $newStateRaw instanceof DocumentState
                ? $newStateRaw->value
                : $newStateRaw;

            DocumentHistory::create([
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'from_state' => $originalStateValue,
                'to_state' => $newStateValue,
            ]);
        }
    }

    public function deleting(Document $document): void
    {
        $currentStateRaw = $document->state;
        $currentState = $currentStateRaw instanceof DocumentState
            ? $currentStateRaw
            : DocumentState::from($currentStateRaw);

        if ($currentState === DocumentState::EXECUTED) {
            throw new InvalidStateTransitionException();
        }
    }
}
