<?php

use App\Domains\Content\Models\KnowledgeBaseArticle;
use App\Models\User;

it('creates and updates knowledge base articles', function () {
    $author = User::factory()->create();

    $article = KnowledgeBaseArticle::factory()->create([
        'author_id' => $author->id,
        'status' => 'draft',
    ]);

    expect($article->type)->toBe('kb');

    $article->update(['status' => 'published']);
    expect($article->fresh()->status)->toBe('published');
});
