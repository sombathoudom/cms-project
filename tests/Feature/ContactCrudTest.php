<?php

use App\Domains\Workflow\Models\Contact;

it('manages contacts', function () {
    $contact = Contact::factory()->create();
    expect($contact->exists())->toBeTrue();

    $contact->update(['phone' => '+123456789']);
    expect($contact->fresh()->phone)->toBe('+123456789');

    $contact->delete();
    expect(Contact::withTrashed()->count())->toBe(1);
});
