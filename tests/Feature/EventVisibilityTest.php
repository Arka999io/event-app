<?php

use App\Actions\BlockUserAction;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows events from users who are not blocked', function () {
    $viewer = User::factory()->create();
    $organizer = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    $events = Event::query()
        ->visibleTo($viewer)
        ->get();

    expect($events->modelKeys())
        ->toContain($event->id);
});

it('hides events when the viewer blocked the organizer', function () {
    $viewer = User::factory()->create();
    $organizer = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    app(BlockUserAction::class)->execute(
        blocker: $viewer,
        blocked: $organizer,
    );

    $events = Event::query()
        ->visibleTo($viewer)
        ->get();

    expect($events->modelKeys())
        ->not->toContain($event->id);
});

it('hides events when the organizer blocked the viewer', function () {
    $viewer = User::factory()->create();
    $organizer = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    app(BlockUserAction::class)->execute(
        blocker: $organizer,
        blocked: $viewer,
    );

    $events = Event::query()
        ->visibleTo($viewer)
        ->get();

    expect($events->modelKeys())
        ->not->toContain($event->id);
});

it('does not show the users own events in the public feed', function () {
    $user = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $user->id,
    ]);

    $events = Event::query()
        ->visibleTo($user)
        ->get();

    expect($events->modelKeys())
        ->not->toContain($event->id);
});
