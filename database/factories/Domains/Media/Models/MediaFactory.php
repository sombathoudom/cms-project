<?php

namespace Database\Factories\Domains\Media\Models;

use App\Domains\Media\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'disk' => 'public',
            'path' => 'uploads/'.fake()->uuid().'.jpg',
            'original_name' => fake()->word().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(1000, 500000),
            'width' => 800,
            'height' => 600,
            'variants' => [],
            'uploaded_by' => User::factory(),
        ];
    }
}
