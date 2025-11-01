<?php

namespace Database\Factories\Domains\Workflow\Models;

use App\Domains\Workflow\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'reference' => Str::upper(Str::random(8)),
            'subject' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => 'open',
            'priority' => 'medium',
            'requester_email' => fake()->safeEmail(),
            'assigned_to' => User::factory(),
            'due_at' => now()->addDays(3),
        ];
    }
}
