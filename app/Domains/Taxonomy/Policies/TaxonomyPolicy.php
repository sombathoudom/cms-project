<?php

namespace App\Domains\Taxonomy\Policies;

use App\Models\User;

class TaxonomyPolicy
{
    public function manage(User $user): bool
    {
        return $user->can('taxonomy.manage');
    }
}
