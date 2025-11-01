<?php

namespace Database\Factories\Domains\Content\Models;

use App\Domains\Content\Models\KnowledgeBaseArticle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnowledgeBaseArticle>
 */
class KnowledgeBaseArticleFactory extends Factory
{
    protected $model = KnowledgeBaseArticle::class;

    public function definition(): array
    {
        return ContentFactory::new()->state([
            'type' => 'kb',
        ])->raw();
    }
}
