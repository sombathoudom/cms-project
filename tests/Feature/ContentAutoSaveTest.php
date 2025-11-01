<?php

use App\Domains\Authentication\Models\Permission;
use App\Domains\Content\Models\KnowledgeBaseArticle;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    if (! Permission::query()->where('name', 'content.update')->where('guard_name', 'web')->exists()) {
        Permission::unguarded(function () {
            Permission::query()->create([
                'id' => (string) Str::uuid(),
                'name' => 'content.update',
                'guard_name' => 'web',
            ]);
        });
    }
});

it('auto-saves knowledge base article content for authorized users', function (): void {
    /** @var User $user */
    $user = User::factory()->create();
    $permission = Permission::query()->firstWhere('name', 'content.update');
    expect($permission)->not->toBeNull();
    expect($permission->getKey())->not->toBe('0');
    $user->permissions()->sync([$permission->getKey()]);

    /** @var KnowledgeBaseArticle $article */
    $article = KnowledgeBaseArticle::factory()->create([
        'author_id' => $user->getKey(),
        'body' => '<p>Initial body</p>',
    ]);

    $payload = [
        'body' => '<p>Updated content body</p>',
        'excerpt' => 'Updated excerpt',
    ];

    $this->actingAs($user);

    $this
        ->postJson(route('api.admin.content.autosave', $article), $payload)
        ->assertOk()
        ->assertJsonPath('data.body', '<p>Updated content body</p>');

    expect($article->refresh()->body)->toBe('<p>Updated content body</p>');

    $this->assertDatabaseHas('content_revisions', [
        'content_id' => $article->getKey(),
        'editor_id' => $user->getKey(),
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'content.autosaved',
        'target_id' => $article->getKey(),
    ]);
});

it('rejects auto-save without required permissions', function (): void {
    /** @var User $viewer */
    $viewer = User::factory()->create();
    $article = KnowledgeBaseArticle::factory()->create();

    $this->actingAs($viewer);

    $this
        ->postJson(route('api.admin.content.autosave', $article), [
            'body' => '<p>Should not persist</p>',
        ])
        ->assertForbidden()
        ->assertJsonPath('error.code', 'ERR_FORBIDDEN');
});

it('validates body field during auto-save', function (): void {
    /** @var User $user */
    $user = User::factory()->create();
    $permission = Permission::query()->firstWhere('name', 'content.update');
    expect($permission)->not->toBeNull();
    expect($permission->getKey())->not->toBe('0');
    $user->permissions()->sync([$permission->getKey()]);
    $article = KnowledgeBaseArticle::factory()->create([
        'author_id' => $user->getKey(),
    ]);

    $this->actingAs($user);

    $this
        ->postJson(route('api.admin.content.autosave', $article), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['body']);
});
