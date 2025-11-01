<?php

namespace Database\Factories\Domains\Security\Models;

use App\Domains\Security\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'tenant_id' => (string) Str::uuid(),
            'actor_id' => User::factory(),
            'action' => fake()->randomElement(['user.created', 'user.updated']),
            'target_type' => User::class,
            'target_id' => (string) Str::uuid(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'payload' => ['field' => 'value'],
        ];
    }
}
