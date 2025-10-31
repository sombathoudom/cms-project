<?php

use App\Domains\Authentication\Models\Role;
use App\Domains\Content\Models\Content;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

it('allows admin to bypass ability checks', function () {
    $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($adminRole);

    $content = Content::factory()->create();

    expect(Gate::forUser($user)->allows('update', $content))->toBeTrue();
});
