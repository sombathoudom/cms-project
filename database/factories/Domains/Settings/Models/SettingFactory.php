<?php

namespace Database\Factories\Domains\Settings\Models;

use App\Domains\Settings\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'key' => fake()->unique()->word(),
            'value' => ['value' => fake()->sentence()],
            'updated_by' => User::factory(),
        ];
    }
}
