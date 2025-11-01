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

it('generates a signed preview link for authorized editors', function (): void {
    /** @var User $user */
    $user = User::factory()->create();
    $permission = Permission::query()->firstWhere('name', 'content.update');
    expect($permission)->not->toBeNull();
    expect($permission->getKey())->not->toBe('0');
    $user->permissions()->sync([$permission->getKey()]);

    /** @var KnowledgeBaseArticle $article */
    $article = KnowledgeBaseArticle::factory()->create([
        'author_id' => $user->getKey(),
        'body' => '<p>Draft body</p>',
    ]);

    $this->actingAs($user);

    $response = $this->postJson(route('api.admin.content.preview-link', $article));

    $response->assertOk()->assertJsonStructure(['data' => ['preview_url', 'expires_at']]);
    $url = $response->json('data.preview_url');

    expect($url)->toContain('/preview/content/'.$article->getKey());
    $this->assertDatabaseHas('content_preview_links', [
        'content_id' => $article->getKey(),
        'created_by' => $user->getKey(),
    ]);

    $this->get($url)->assertOk()->assertSee($article->title);
});

it('prevents generating preview links without permission', function (): void {
    $viewer = User::factory()->create();
    $article = KnowledgeBaseArticle::factory()->create();

    $this->actingAs($viewer);

    $this
        ->postJson(route('api.admin.content.preview-link', $article))
        ->assertForbidden()
        ->assertJsonPath('error.code', 'ERR_FORBIDDEN');
});

it('rejects unsigned preview access', function (): void {
    $article = KnowledgeBaseArticle::factory()->create();
    $url = url('/preview/content/'.$article->getKey().'/'.Str::uuid());

    $this->get($url)->assertForbidden();
});
