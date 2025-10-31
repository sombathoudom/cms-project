<?php

namespace App\Domains\Security\Policies;

use App\Domains\Security\Models\AuditLog;
use App\Models\User;

class AuditPolicy
{
    public function view(User $user, AuditLog $log): bool
    {
        return $user->can('audit.view');
    }
}
