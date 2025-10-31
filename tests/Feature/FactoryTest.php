<?php

use App\Domains\Content\Models\Content;
use App\Domains\Media\Models\Media;
use App\Domains\Settings\Models\Setting;
use App\Domains\Workflow\Models\Contact;
use App\Domains\Workflow\Models\Ticket;
use App\Models\User;

it('creates base models via factories', function () {
    $user = User::factory()->create();
    expect($user->id)->not->toBeNull();

    $media = Media::factory()->create();
    expect($media->id)->not->toBeNull();

    $content = Content::factory()->create();
    expect($content->slug)->not->toBeEmpty();

    $ticket = Ticket::factory()->create();
    expect($ticket->reference)->not->toBeEmpty();

    $contact = Contact::factory()->create();
    expect($contact->email)->not->toBeEmpty();

    $setting = Setting::factory()->create();
    expect($setting->key)->not->toBeEmpty();
});
