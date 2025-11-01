<?php

namespace Database\Factories\Domains\Content\Models;

use App\Domains\Content\Models\Content;
use App\Domains\Media\Models\Media;
use App\Domains\Taxonomy\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Content>
 */
class ContentFactory extends Factory
{
    protected $model = Content::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'type' => 'post',
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'excerpt' => fake()->paragraph(),
            'body' => fake()->paragraphs(3, true),
            'featured_media_id' => Media::factory(),
            'category_id' => Category::factory(),
            'status' => 'draft',
            'publish_at' => now()->addDay(),
            'author_id' => User::factory(),
            'reviewer_id' => null,
            'settings_snapshot' => ['layout' => 'default'],
        ];
    }
}
