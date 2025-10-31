<?php

namespace App\Domains\Workflow\Policies;

use App\Domains\Workflow\Models\Workflow;
use App\Models\User;

class WorkflowPolicy
{
    public function view(User $user, Workflow $workflow): bool
    {
        return $user->can('workflow.view');
    }

    public function update(User $user, Workflow $workflow): bool
    {
        return $user->can('workflow.update');
    }
}
