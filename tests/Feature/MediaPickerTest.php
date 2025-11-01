<?php

use App\Domains\Authentication\Models\Permission;
use App\Domains\Authentication\Models\Role;
use App\Domains\Content\Models\Content;
use App\Domains\Media\Models\Media;
use App\Domains\Media\Models\MediaUsage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    config(['filesystems.default' => 'public']);

    $this->mediaView = Permission::query()->create([
        'name' => 'media.view',
        'guard_name' => 'web',
    ]);

    $this->mediaCreate = Permission::query()->create([
        'name' => 'media.create',
        'guard_name' => 'web',
    ]);

    $this->adminRole = Role::query()->create([
        'name' => 'Admin',
        'guard_name' => 'web',
    ]);

    $this->editorRole = Role::query()->create([
        'name' => 'Editor',
        'guard_name' => 'web',
    ]);

    $this->authorRole = Role::query()->create([
        'name' => 'Author',
        'guard_name' => 'web',
    ]);

    $this->adminRole->syncPermissions([$this->mediaView, $this->mediaCreate]);
    $this->editorRole->syncPermissions([$this->mediaView]);

    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function mediaUser(Role $role, array $attributes = [], array $permissions = []): User
{
    $user = User::factory()->create($attributes);
    $user->assignRole($role);

    collect($permissions)->each(function ($permission) use ($user): void {
        DB::table('model_has_permissions')->insert([
            'permission_id' => $permission->getKey(),
            'model_id' => $user->getKey(),
            'model_type' => User::class,
        ]);
    });

    return $user;
}

it('lists media assets scoped to the authenticated tenant', function (): void {
    Storage::fake('public');

    $tenantId = (string) Str::uuid();
    $admin = mediaUser($this->adminRole, ['tenant_id' => $tenantId], [$this->mediaView, $this->mediaCreate]);

    Media::factory()->create([
        'tenant_id' => $tenantId,
        'mime_type' => 'image/jpeg',
    ]);

    Media::factory()->create([
        'tenant_id' => (string) Str::uuid(),
        'mime_type' => 'image/jpeg',
    ]);

    $this->actingAs($admin);

    $response = $this->getJson('/api/v1/admin/media?type=image/');

    $response->assertOk();

    $payload = $response->json();

    $collection = collect($payload['data']);

    expect($payload['data'])->toBeArray()
        ->and($collection->every(fn (array $item) => isset($item['id'], $item['url'])))->toBeTrue();

    $collection->each(fn (array $item) => expect(Str::startsWith($item['mime_type'], 'image/'))->toBeTrue());

    expect(collect($payload['data'])->pluck('id')->count())->toBe(1);
})->name('E3-F1-I5 lists media assets scoped to tenant');

it('rejects media listing for unauthenticated and unauthorized actors', function (): void {
    $response = $this->getJson('/api/v1/admin/media');

    $response->assertStatus(401)
        ->assertExactJson([
            'error' => [
                'code' => 'ERR_UNAUTHENTICATED',
                'message' => 'Authentication required.',
            ],
        ]);

    $author = mediaUser($this->authorRole);
    $this->actingAs($author);

    $forbidden = $this->getJson('/api/v1/admin/media');

    $forbidden->assertStatus(403)
        ->assertExactJson([
            'error' => [
                'code' => 'ERR_FORBIDDEN',
                'message' => 'You do not have permission to view media assets.',
            ],
        ]);
})->name('E3-F1-I5 enforces media listing permissions');

it('uploads media assets and returns resource metadata', function (): void {
    Storage::fake('public');

    $tenantId = (string) Str::uuid();
    $admin = mediaUser($this->adminRole, ['tenant_id' => $tenantId], [$this->mediaView, $this->mediaCreate]);

    $this->actingAs($admin);

    $file = UploadedFile::fake()->image('photo.jpg', 600, 400);

    $response = $this->post('/api/v1/admin/media', [
        'file' => $file,
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertCreated();

    $payload = $response->json();

    $mediaId = $payload['data']['id'] ?? null;
    $stored = Media::query()->find($mediaId);

    expect($payload['data']['mime_type'])->toEqual('image/jpeg')
        ->and($stored)->not->toBeNull()
        ->and(Storage::disk('public')->exists($stored->path))->toBeTrue();
})->name('E3-F1-I5 uploads media assets');

it('embeds media into content and records usage', function (): void {
    $tenantId = (string) Str::uuid();
    $admin = mediaUser($this->adminRole, ['tenant_id' => $tenantId], [$this->mediaView, $this->mediaCreate]);
    $this->actingAs($admin);

    $media = Media::factory()->create([
        'tenant_id' => $tenantId,
        'mime_type' => 'image/png',
    ]);

    $content = Content::factory()->create([
        'tenant_id' => $tenantId,
        'author_id' => $admin->id,
    ]);

    $response = $this->postJson("/api/v1/admin/content/{$content->getKey()}/media", [
        'media_id' => $media->getKey(),
        'alt_text' => 'Diagram of architecture',
        'position' => 2,
    ]);

    $response->assertOk();

    $payload = $response->json('data');

    expect($payload['markup'])->toContain($media->url)
        ->and($payload['markup'])->toContain('Diagram of architecture');

    $usage = MediaUsage::query()->where('media_id', $media->getKey())->first();
    expect($usage)->not->toBeNull()
        ->and($usage->context)->toEqual('body')
        ->and($usage->alt_text)->toEqual('Diagram of architecture');
})->name('E3-F1-I5 embeds media and records usage');

it('prevents embedding media from another tenant', function (): void {
    $tenantId = (string) Str::uuid();
    $admin = mediaUser($this->adminRole, ['tenant_id' => $tenantId], [$this->mediaView, $this->mediaCreate]);
    $this->actingAs($admin);

    $foreignMedia = Media::factory()->create([
        'tenant_id' => (string) Str::uuid(),
    ]);

    $content = Content::factory()->create([
        'tenant_id' => $tenantId,
        'author_id' => $admin->id,
    ]);

    $response = $this->postJson("/api/v1/admin/content/{$content->getKey()}/media", [
        'media_id' => $foreignMedia->getKey(),
    ]);

    $response->assertStatus(404)
        ->assertExactJson([
            'error' => [
                'code' => 'ERR_NOT_FOUND',
                'message' => 'Media asset not found for this tenant.',
            ],
        ]);
})->name('E3-F1-I5 enforces tenant scoped embeds');

it('enforces media policy matrix for admin editor and author roles', function (): void {
    $admin = mediaUser($this->adminRole, [], [$this->mediaView, $this->mediaCreate]);
    $editor = mediaUser($this->editorRole, [], [$this->mediaView]);
    $author = mediaUser($this->authorRole);

    $editor->refresh();
    $author->refresh();

    expect($editor->hasPermissionTo('media.view'))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('viewAny', Media::class))->toBeTrue()
        ->and(Gate::forUser($editor)->allows('viewAny', Media::class))->toBeTrue()
        ->and(Gate::forUser($author)->allows('viewAny', Media::class))->toBeFalse();
})->name('E3-F1-I5 media policy matrix');
