<?php

namespace App\Enums;

enum DocumentState: string
{
    case DRAFT = 'Draft';
    case PENDING_LEGAL_REVIEW = 'Pending Legal Review';
    case MANAGER_APPROVED = 'Manager Approved';
    case EXECUTED = 'Executed';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::DRAFT => $target === self::PENDING_LEGAL_REVIEW,
            self::PENDING_LEGAL_REVIEW => $target === self::MANAGER_APPROVED,
            self::MANAGER_APPROVED => $target === self::EXECUTED,
            self::EXECUTED => false,
        };
    }

    public function requiredRoleForTransition(): ?string
    {
        return match ($this) {
            self::PENDING_LEGAL_REVIEW => 'Legal_Compliance',
            self::MANAGER_APPROVED => 'Manager',
            default => null,
        };
    }
}
