<?php

use App\Domains\Workflow\Models\Ticket;
use App\Models\User;

it('performs CRUD on tickets', function () {
    $user = User::factory()->create();

    $ticket = Ticket::factory()->create([
        'assigned_to' => $user->id,
        'status' => 'open',
    ]);

    expect($ticket->status)->toBe('open');

    $ticket->update(['status' => 'closed']);
    expect($ticket->fresh()->status)->toBe('closed');

    $ticketId = $ticket->id;
    $ticket->delete();

    expect(Ticket::withTrashed()->find($ticketId))->not->toBeNull();
});
