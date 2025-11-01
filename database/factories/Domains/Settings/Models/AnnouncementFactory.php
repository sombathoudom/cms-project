<?php

namespace Database\Factories\Domains\Settings\Models;

use App\Domains\Settings\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'title' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'starts_at' => now(),
            'ends_at' => now()->addWeek(),
            'is_active' => true,
        ];
    }
}
