<?php

use App\Domains\Authentication\Models\Permission;
use App\Domains\Authentication\Models\Role;
use App\Domains\Content\Models\Content;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

it('enforces policy rules via permissions', function () {
    $permission = Permission::create(['name' => 'content.update', 'guard_name' => 'web']);
    $role = Role::create(['name' => 'Agent', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    $user = User::factory()->create();
    $user->assignRole($role);

    $content = Content::factory()->create(['author_id' => $user->id]);

    expect($user->can('content.update'))->toBeTrue();
    expect(Gate::forUser($user)->allows('update', $content))->toBeTrue();

    $viewer = User::factory()->create();
    expect(Gate::forUser($viewer)->allows('update', $content))->toBeFalse();
});
