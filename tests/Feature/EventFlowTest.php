<?php

use App\Actions\CancelEventAction;
use App\Actions\CompleteEventAction;
use App\Actions\CreateEventAction;
use App\Actions\PublishEventAction;
use App\Actions\StartEventAction;
use App\DTO\CreateEventDTO;
use App\Enums\EventStatus;
use App\Enums\GenderPreference;
use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a verified user to create a draft event', function () {
    $organizer = User::factory()->create();
    $category = Category::factory()->create();

    $data = new CreateEventDTO(
        categoryId: $category->id,
        title: 'Weekend Hiking',
        description: 'A group hiking event.',
        startsAt: CarbonImmutable::now()->addWeek(),
        endsAt: CarbonImmutable::now()->addWeek()->addHours(4),
        meetingAddress: 'Central Station',
        venueAddress: 'National Park',
        minAge: 18,
        maxAge: 40,
        genderPreference: GenderPreference::Any,
        spotsTotal: 10,
    );

    $event = app(CreateEventAction::class)->execute(
        organizer: $organizer,
        data: $data,
    );

    expect($event)
        ->organizer_id->toBe($organizer->id)
        ->category_id->toBe($category->id)
        ->status->toBe(EventStatus::Draft)
        ->spots_total->toBe(10)
        ->spots_taken->toBe(0)
        ->published_at->toBeNull();

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'organizer_id' => $organizer->id,
        'status' => EventStatus::Draft->value,
        'spots_taken' => 0,
    ]);
});

it('does not allow an unverified user to create an event', function () {
    $organizer = User::factory()->unverified()->create();
    $category = Category::factory()->create();

    $data = new CreateEventDTO(
        categoryId: $category->id,
        title: 'Weekend Hiking',
        description: 'A group hiking event.',
        startsAt: CarbonImmutable::now()->addWeek(),
        endsAt: null,
        meetingAddress: null,
        venueAddress: 'National Park',
        minAge: null,
        maxAge: null,
        genderPreference: GenderPreference::Any,
        spotsTotal: 10,
    );

    expect(fn () => app(CreateEventAction::class)->execute(
        organizer: $organizer,
        data: $data,
    ))->toThrow(
        DomainException::class,
        'Email must be verified.',
    );

    $this->assertDatabaseCount('events', 0);
});

it('does not allow creating an event in an inactive category', function () {
    $organizer = User::factory()->create();

    $category = Category::factory()->create([
        'is_active' => false,
    ]);

    $data = new CreateEventDTO(
        categoryId: $category->id,
        title: 'Weekend Hiking',
        description: 'A group hiking event.',
        startsAt: CarbonImmutable::now()->addWeek(),
        endsAt: null,
        meetingAddress: null,
        venueAddress: 'National Park',
        minAge: null,
        maxAge: null,
        genderPreference: GenderPreference::Any,
        spotsTotal: 10,
    );

    expect(fn () => app(CreateEventAction::class)->execute(
        organizer: $organizer,
        data: $data,
    ))->toThrow(
        DomainException::class,
        'The selected category is inactive.',
    );

    $this->assertDatabaseCount('events', 0);
});

it('does not allow creating an event in the past', function () {
    $organizer = User::factory()->create();
    $category = Category::factory()->create();

    $data = new CreateEventDTO(
        categoryId: $category->id,
        title: 'Weekend Hiking',
        description: 'A group hiking event.',
        startsAt: CarbonImmutable::now()->subDay(),
        endsAt: null,
        meetingAddress: null,
        venueAddress: 'National Park',
        minAge: null,
        maxAge: null,
        genderPreference: GenderPreference::Any,
        spotsTotal: 10,
    );

    expect(fn () => app(CreateEventAction::class)->execute(
        organizer: $organizer,
        data: $data,
    ))->toThrow(
        DomainException::class,
        'Event start time must be in the future.',
    );

    $this->assertDatabaseCount('events', 0);
});

it('does not allow the event to end before it starts', function () {
    $organizer = User::factory()->create();
    $category = Category::factory()->create();

    $startsAt = CarbonImmutable::now()->addWeek();

    $data = new CreateEventDTO(
        categoryId: $category->id,
        title: 'Weekend Hiking',
        description: 'A group hiking event.',
        startsAt: $startsAt,
        endsAt: $startsAt->subHour(),
        meetingAddress: null,
        venueAddress: 'National Park',
        minAge: null,
        maxAge: null,
        genderPreference: GenderPreference::Any,
        spotsTotal: 10,
    );

    expect(fn () => app(CreateEventAction::class)->execute(
        organizer: $organizer,
        data: $data,
    ))->toThrow(
        DomainException::class,
        'Event end time must be after the start time.',
    );

    $this->assertDatabaseCount('events', 0);
});

it('does not allow minimum age to be greater than maximum age', function () {
    $organizer = User::factory()->create();
    $category = Category::factory()->create();

    $data = new CreateEventDTO(
        categoryId: $category->id,
        title: 'Weekend Hiking',
        description: 'A group hiking event.',
        startsAt: CarbonImmutable::now()->addWeek(),
        endsAt: null,
        meetingAddress: null,
        venueAddress: 'National Park',
        minAge: 40,
        maxAge: 18,
        genderPreference: GenderPreference::Any,
        spotsTotal: 10,
    );

    expect(fn () => app(CreateEventAction::class)->execute(
        organizer: $organizer,
        data: $data,
    ))->toThrow(
        DomainException::class,
        'Minimum age cannot be greater than maximum age.',
    );

    $this->assertDatabaseCount('events', 0);
});

it('requires at least one event spot', function () {
    $organizer = User::factory()->create();
    $category = Category::factory()->create();

    $data = new CreateEventDTO(
        categoryId: $category->id,
        title: 'Weekend Hiking',
        description: 'A group hiking event.',
        startsAt: CarbonImmutable::now()->addWeek(),
        endsAt: null,
        meetingAddress: null,
        venueAddress: 'National Park',
        minAge: null,
        maxAge: null,
        genderPreference: GenderPreference::Any,
        spotsTotal: 0,
    );

    expect(fn () => app(CreateEventAction::class)->execute(
        organizer: $organizer,
        data: $data,
    ))->toThrow(
        DomainException::class,
        'Event must have at least one spot.',
    );

    $this->assertDatabaseCount('events', 0);
});

it('allows a draft event to be published', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::Draft,
        'starts_at' => now()->addWeek(),
        'published_at' => null,
    ]);

    $published = app(PublishEventAction::class)
        ->execute($event);

    expect($published->status)
        ->toBe(EventStatus::Published);

    expect($published->published_at)
        ->not->toBeNull();

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'status' => EventStatus::Published->value,
    ]);
});

it('does not allow a published event to be published again', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::Published,
        'starts_at' => now()->addWeek(),
        'published_at' => now(),
    ]);

    expect(fn () => app(PublishEventAction::class)->execute($event))
        ->toThrow(
            DomainException::class,
            'Only draft events can be published.',
        );
});

it('does not allow publishing an event with a past start time', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::Draft,
        'starts_at' => now()->subHour(),
        'published_at' => null,
    ]);

    expect(fn () => app(PublishEventAction::class)->execute($event))
        ->toThrow(
            DomainException::class,
            'An event with a past start time cannot be published.',
        );

    expect($event->refresh()->status)
        ->toBe(EventStatus::Draft);
});

it('allows a published event to be cancelled', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::Published,
    ]);

    $cancelled = app(CancelEventAction::class)
        ->execute($event);

    expect($cancelled->status)
        ->toBe(EventStatus::Cancelled);
});

it('does not allow a cancelled event to be cancelled again', function () {
    $event = Event::factory()->cancelled()->create();

    expect(fn () => app(CancelEventAction::class)->execute($event))
        ->toThrow(
            DomainException::class,
            'This event cannot be cancelled.',
        );
});

it('allows a started published event to be completed', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::Published,
        'starts_at' => now()->subHours(2),
    ]);

    $completed = app(CompleteEventAction::class)
        ->execute($event);

    expect($completed->status)
        ->toBe(EventStatus::Completed);
});

it('does not allow an event to be completed before it starts', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::Published,
        'starts_at' => now()->addDay(),
    ]);

    expect(fn () => app(CompleteEventAction::class)->execute($event))
        ->toThrow(
            DomainException::class,
            'An event cannot be completed before it starts.',
        );

    expect($event->refresh()->status)
        ->toBe(EventStatus::Published);
});

it('does not allow a draft event to be completed', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::Draft,
        'starts_at' => now()->subHour(),
    ]);

    expect(fn () => app(CompleteEventAction::class)->execute($event))
        ->toThrow(
            DomainException::class,
            'This event cannot be completed.',
        );
});

it('allows a published event to be started after its start time', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::Published,
        'starts_at' => now()->subMinute(),
    ]);

    $started = app(StartEventAction::class)
        ->execute($event);

    expect($started->status)
        ->toBe(EventStatus::Ongoing);
});

it('does not allow an event to be started before its start time', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::Published,
        'starts_at' => now()->addDay(),
    ]);

    expect(fn () => app(StartEventAction::class)->execute($event))
        ->toThrow(
            DomainException::class,
            'An event cannot be started before its start time.',
        );

    expect($event->refresh()->status)
        ->toBe(EventStatus::Published);
});

it('does not allow a draft event to be started', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::Draft,
        'starts_at' => now()->subMinute(),
    ]);

    expect(fn () => app(StartEventAction::class)->execute($event))
        ->toThrow(
            DomainException::class,
            'Only published events can be started.',
        );

    expect($event->refresh()->status)
        ->toBe(EventStatus::Draft);
});
