<?php

use App\Actions\BlockUserAction;
use App\Actions\CreateApplicationAction;
use App\Models\Application;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('does not let a user apply when the organizer blocked them', function () {
    $organizer = User::factory()->create();
    $applicant = User::factory()->create();
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);

    app(BlockUserAction::class)->execute(
        blocker: $organizer,
        blocked: $applicant,
    );

    expect(fn () => app(CreateApplicationAction::class)->execute($applicant, $event))
        ->toThrow(DomainException::class);

    expect(Application::query()->count())->toBe(0);
});

it('does not let a user apply when they blocked the organizer', function () {
    $organizer = User::factory()->create();
    $applicant = User::factory()->create();
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);

    app(BlockUserAction::class)->execute(
        blocker: $applicant,
        blocked: $organizer,
    );

    expect(fn () => app(CreateApplicationAction::class)->execute($applicant, $event))
        ->toThrow(DomainException::class);

    expect(Application::query()->count())->toBe(0);
});

it('returns 403 on the apply endpoint when the organizer blocked the user', function () {
    Notification::fake();

    $organizer = User::factory()->create();
    $applicant = User::factory()->create();
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);

    app(BlockUserAction::class)->execute(
        blocker: $organizer,
        blocked: $applicant,
    );

    $this->actingAs($applicant)
        ->post(route('events.apply', $event))
        ->assertForbidden();

    expect(Application::query()->count())->toBe(0);
});

it('still lets an unblocked user apply', function () {
    Notification::fake();

    $organizer = User::factory()->create();
    $applicant = User::factory()->create();
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);

    $this->actingAs($applicant)
        ->post(route('events.apply', $event))
        ->assertRedirect(route('events.show', $event));

    expect(
        Application::query()
            ->where('event_id', $event->id)
            ->where('applicant_id', $applicant->id)
            ->exists()
    )->toBeTrue();
});
