<?php

namespace App\Domains\Authentication\Observers;

use App\Domains\Security\Services\AuditLogger;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        app(AuditLogger::class)->log('user.created', $user, [
            'attributes' => $user->only(['name', 'email', 'status']),
        ]);
    }

    public function updated(User $user): void
    {
        $changes = collect($user->getChanges())
            ->only(['name', 'email', 'status'])
            ->toArray();

        if ($changes === []) {
            return;
        }

        app(AuditLogger::class)->log('user.updated', $user, [
            'changes' => $changes,
        ]);
    }

    public function deleted(User $user): void
    {
        app(AuditLogger::class)->log('user.deleted', $user, []);
    }

    public function restored(User $user): void
    {
        app(AuditLogger::class)->log('user.restored', $user, []);
    }
}
