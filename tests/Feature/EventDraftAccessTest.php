<?php

use App\Actions\StartConversationAction;
use App\Enums\ReportReason;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

it('lets the organizer view their own draft', function () {
    $organizer = User::factory()->create();
    $event = Event::factory()->draft()->create([
        'organizer_id' => $organizer->id,
    ]);

    $this->actingAs($organizer)
        ->get(route('events.show', $event))
        ->assertOk();
});

it('returns 404 for a draft when requested by another user', function () {
    $event = Event::factory()->draft()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('events.show', $event))
        ->assertNotFound();
});

it('returns 404 for a draft when requested by a guest', function () {
    $event = Event::factory()->draft()->create();

    $this->get(route('events.show', $event))->assertNotFound();
});

it('still shows published events to guests', function () {
    $event = Event::factory()->create();

    $this->get(route('events.show', $event))->assertOk();
});

it('still shows cancelled events to guests', function () {
    $event = Event::factory()->cancelled()->create();

    $this->get(route('events.show', $event))->assertOk();
});

it('does not expose the report form of a draft event', function () {
    $event = Event::factory()->draft()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('reports.events.create', $event))
        ->assertNotFound();
});

it('does not allow reporting a draft event', function () {
    $event = Event::factory()->draft()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('reports.events.store', $event), [
            'reason' => ReportReason::Spam->value,
        ])
        ->assertNotFound();

    $this->assertDatabaseCount('reports', 0);
});

it('does not allow starting a conversation about a draft event', function () {
    $event = Event::factory()->draft()->create();
    $user = User::factory()->create();

    expect(fn () => app(StartConversationAction::class)->execute($user, $event))
        ->toThrow(DomainException::class);
});
