<?php

namespace Database\Factories\Domains\Taxonomy\Models;

use App\Domains\Taxonomy\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => fake()->word(),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'seo' => ['title' => fake()->sentence()],
        ];
    }
}
