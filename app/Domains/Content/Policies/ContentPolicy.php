<?php

namespace App\Domains\Content\Policies;

use App\Domains\Content\Models\Content;
use App\Models\User;

class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('content.view');
    }

    public function view(User $user, Content $content): bool
    {
        return $user->can('content.view');
    }

    public function create(User $user): bool
    {
        return $user->can('content.create');
    }

    public function update(User $user, Content $content): bool
    {
        return $user->can('content.update');
    }

    public function delete(User $user, Content $content): bool
    {
        return $user->can('content.delete');
    }
}
