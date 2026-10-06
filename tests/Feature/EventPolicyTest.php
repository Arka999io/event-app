<?php

use App\Actions\BlockUserAction;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

it('allows a user to view an event from an unrelated organizer', function () {
    $viewer = User::factory()->create();
    $organizer = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    expect($viewer->can('view', $event))->toBeTrue();
});

it('allows an organizer to view their own event', function () {
    $organizer = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    expect($organizer->can('view', $event))->toBeTrue();
});

it('denies viewing an event when the viewer blocked the organizer', function () {
    $viewer = User::factory()->create();
    $organizer = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    app(BlockUserAction::class)->execute(
        blocker: $viewer,
        blocked: $organizer,
    );

    expect($viewer->can('view', $event))->toBeFalse();
});

it('denies viewing an event when the organizer blocked the viewer', function () {
    $viewer = User::factory()->create();
    $organizer = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    app(BlockUserAction::class)->execute(
        blocker: $organizer,
        blocked: $viewer,
    );

    expect($viewer->can('view', $event))->toBeFalse();
});

it('allows only the organizer to publish an event', function () {
    $organizer = User::factory()->create();
    $stranger = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
        'status' => EventStatus::Draft,
    ]);

    expect($organizer->can('publish', $event))->toBeTrue()
        ->and($stranger->can('publish', $event))->toBeFalse();
});

it('allows only the organizer to complete an event', function () {
    $organizer = User::factory()->create();
    $stranger = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    expect($organizer->can('complete', $event))->toBeTrue()
        ->and($stranger->can('complete', $event))->toBeFalse();
});

it('allows only the organizer to start an event', function () {
    $organizer = User::factory()->create();
    $stranger = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    expect($organizer->can('start', $event))->toBeTrue()
        ->and($stranger->can('start', $event))->toBeFalse();
});

it('denies viewing a draft to everyone except the organizer', function () {
    $organizer = User::factory()->create();
    $stranger = User::factory()->create();

    $event = Event::factory()->draft()->create([
        'organizer_id' => $organizer->id,
    ]);

    expect($organizer->can('view', $event))->toBeTrue()
        ->and($stranger->can('view', $event))->toBeFalse()
        ->and(Gate::allows('view', $event))->toBeFalse();
});
