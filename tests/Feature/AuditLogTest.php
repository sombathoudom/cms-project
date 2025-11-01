<?php

use App\Domains\Authentication\Models\Permission;
use App\Domains\Authentication\Models\Role;
use App\Domains\Security\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->auditPermission = Permission::query()->create([
        'name' => 'audit.view',
        'guard_name' => 'web',
    ]);

    $this->adminRole = Role::query()->create([
        'name' => 'Admin',
        'guard_name' => 'web',
    ]);

    $this->agentRole = Role::query()->create([
        'name' => 'Agent',
        'guard_name' => 'web',
    ]);

    $this->viewerRole = Role::query()->create([
        'name' => 'Viewer',
        'guard_name' => 'web',
    ]);

    $this->adminRole->syncPermissions([$this->auditPermission]);
});

function createUserWithRole(Role $role, array $attributes = []): User
{
    $user = User::factory()->create($attributes);
    $user->assignRole($role);

    return $user;
}

it('logs user lifecycle actions with the acting administrator', function (): void {
    $tenantId = (string) Str::uuid();
    $admin = createUserWithRole($this->adminRole, ['tenant_id' => $tenantId]);
    $this->actingAs($admin);

    AuditLog::query()->forceDelete();

    $managedUser = User::factory()->create([
        'tenant_id' => $tenantId,
        'email' => 'managed@example.com',
    ]);

    $managedUser->update(['name' => 'Updated Name']);
    $managedUser->delete();
    $managedUser->restore();

    expect(AuditLog::where('action', 'user.created')->count())->toBe(1)
        ->and(AuditLog::where('action', 'user.updated')->count())->toBe(1)
        ->and(AuditLog::where('action', 'user.deleted')->count())->toBe(1)
        ->and(AuditLog::where('action', 'user.restored')->count())->toBe(1);

    $createLog = AuditLog::where('action', 'user.created')->firstOrFail();

    expect($createLog->actor_id)->toEqual($admin->id)
        ->and($createLog->tenant_id)->toEqual($tenantId)
        ->and($createLog->payload['attributes']['email'])->toEqual('managed@example.com')
        ->and($createLog->payload['attributes'])->not->toHaveKey('password');
})->name('E1-F1-I6 logs user lifecycle actions with the acting administrator');

it('returns paginated audit logs for authorized admins only', function (): void {
    $tenantId = (string) Str::uuid();
    $admin = createUserWithRole($this->adminRole, ['tenant_id' => $tenantId]);
    $otherTenant = (string) Str::uuid();

    $this->actingAs($admin);

    AuditLog::factory()->create([
        'tenant_id' => $tenantId,
        'actor_id' => $admin->id,
        'target_id' => $admin->id,
        'target_type' => User::class,
    ]);

    AuditLog::factory()->create([
        'tenant_id' => $otherTenant,
    ]);

    $response = $this->getJson('/api/v1/admin/audit-logs');

    $response->assertOk();

    $payload = $response->json();

    expect($payload['data'])->toBeArray()
        ->and(count($payload['data']))->toBeGreaterThanOrEqual(1);

    $actorMatches = collect($payload['data'])->contains(fn (array $item) => ($item['actor']['id'] ?? null) === $admin->id);

    expect($actorMatches)->toBeTrue();
})->name('E1-F1-I6 paginated audit logs for authorized admins');

it('rejects unauthenticated and unauthorized access with standard errors', function (): void {
    $response = $this->getJson('/api/v1/admin/audit-logs');

    $response->assertStatus(401)
        ->assertExactJson([
            'error' => [
                'code' => 'ERR_UNAUTHENTICATED',
                'message' => 'Authentication required.',
            ],
        ]);

    $agent = createUserWithRole($this->agentRole);
    $this->actingAs($agent);

    $forbidden = $this->getJson('/api/v1/admin/audit-logs');

    $forbidden->assertStatus(403)
        ->assertExactJson([
            'error' => [
                'code' => 'ERR_FORBIDDEN',
                'message' => 'You do not have permission to view audit logs.',
            ],
        ]);
})->name('E1-F1-I6 enforces audit log API errors');

it('enforces policy matrix for admin agent and viewer roles', function (): void {
    $admin = createUserWithRole($this->adminRole);
    $agent = createUserWithRole($this->agentRole);
    $viewer = createUserWithRole($this->viewerRole);
    $log = AuditLog::factory()->create();

    expect(Gate::forUser($admin)->allows('view', $log))->toBeTrue()
        ->and(Gate::forUser($agent)->allows('view', $log))->toBeFalse()
        ->and(Gate::forUser($viewer)->allows('view', $log))->toBeFalse();
})->name('E1-F1-I6 audit log policy matrix');
