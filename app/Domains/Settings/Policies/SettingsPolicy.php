<?php

namespace App\Domains\Settings\Policies;

use App\Domains\Settings\Models\Setting;
use App\Models\User;

class SettingsPolicy
{
    public function view(User $user, Setting $setting): bool
    {
        return $user->can('settings.view');
    }

    public function update(User $user, Setting $setting): bool
    {
        return $user->can('settings.update');
    }
}
