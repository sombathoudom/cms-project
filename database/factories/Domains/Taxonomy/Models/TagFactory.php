<?php

namespace Database\Factories\Domains\Taxonomy\Models;

use App\Domains\Taxonomy\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => fake()->word(),
            'slug' => fake()->unique()->slug(),
        ];
    }
}
