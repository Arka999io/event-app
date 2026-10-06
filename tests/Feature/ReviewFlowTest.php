<?php

use App\Actions\CreateReviewAction;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows an accepted participant to review the organizer after the event', function () {
    $organizer = User::factory()->create();
    $participant = User::factory()->create();

    $event = Event::factory()
        ->completed()
        ->create([
            'organizer_id' => $organizer->id,
        ]);

    Application::factory()->create([
        'event_id' => $event->id,
        'applicant_id' => $participant->id,
        'status' => ApplicationStatus::Accepted,
    ]);

    $review = app(CreateReviewAction::class)->execute(
        author: $participant,
        subject: $organizer,
        event: $event,
        rating: 5,
        comment: 'Great event!',
    );

    expect($review)
        ->author_id->toBe($participant->id)
        ->subject_id->toBe($organizer->id)
        ->event_id->toBe($event->id)
        ->rating->toBe(5)
        ->comment->toBe('Great event!');

    $this->assertDatabaseHas('reviews', [
        'event_id' => $event->id,
        'author_id' => $participant->id,
        'subject_id' => $organizer->id,
        'rating' => 5,
    ]);
});

it('allows the organizer to review an accepted participant after the event', function () {
    $organizer = User::factory()->create();
    $participant = User::factory()->create();

    $event = Event::factory()
        ->completed()
        ->create([
            'organizer_id' => $organizer->id,
        ]);

    Application::factory()->create([
        'event_id' => $event->id,
        'applicant_id' => $participant->id,
        'status' => ApplicationStatus::Accepted,
    ]);

    $review = app(CreateReviewAction::class)->execute(
        author: $organizer,
        subject: $participant,
        event: $event,
        rating: 4,
        comment: 'Great participant!',
    );

    expect($review)
        ->author_id->toBe($organizer->id)
        ->subject_id->toBe($participant->id)
        ->rating->toBe(4);
});

it('does not allow reviews before the event is completed', function () {
    $organizer = User::factory()->create();
    $participant = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    Application::factory()->create([
        'event_id' => $event->id,
        'applicant_id' => $participant->id,
        'status' => ApplicationStatus::Accepted,
    ]);

    expect(fn () => app(CreateReviewAction::class)->execute(
        author: $participant,
        subject: $organizer,
        event: $event,
        rating: 5,
    ))->toThrow(
        DomainException::class,
        'Reviews can only be created for completed events.',
    );

    $this->assertDatabaseCount('reviews', 0);
});

it('does not allow a rating outside the range from 1 to 5', function () {
    $organizer = User::factory()->create();
    $participant = User::factory()->create();

    $event = Event::factory()
        ->completed()
        ->create([
            'organizer_id' => $organizer->id,
        ]);

    expect(fn () => app(CreateReviewAction::class)->execute(
        author: $participant,
        subject: $organizer,
        event: $event,
        rating: 6,
    ))->toThrow(
        DomainException::class,
        'Rating must be between 1 and 5.',
    );

    $this->assertDatabaseCount('reviews', 0);
});

it('does not allow a non-accepted participant to review the organizer', function () {
    $organizer = User::factory()->create();
    $participant = User::factory()->create();

    $event = Event::factory()
        ->completed()
        ->create([
            'organizer_id' => $organizer->id,
        ]);

    Application::factory()->create([
        'event_id' => $event->id,
        'applicant_id' => $participant->id,
        'status' => ApplicationStatus::Pending,
    ]);

    expect(fn () => app(CreateReviewAction::class)->execute(
        author: $participant,
        subject: $organizer,
        event: $event,
        rating: 5,
    ))->toThrow(
        DomainException::class,
        'The participant must have an accepted application.',
    );

    $this->assertDatabaseCount('reviews', 0);
});

it('does not allow duplicate reviews for the same event and user', function () {
    $organizer = User::factory()->create();
    $participant = User::factory()->create();

    $event = Event::factory()
        ->completed()
        ->create([
            'organizer_id' => $organizer->id,
        ]);

    Application::factory()->create([
        'event_id' => $event->id,
        'applicant_id' => $participant->id,
        'status' => ApplicationStatus::Accepted,
    ]);

    $action = app(CreateReviewAction::class);

    $action->execute(
        author: $participant,
        subject: $organizer,
        event: $event,
        rating: 5,
    );

    expect(fn () => $action->execute(
        author: $participant,
        subject: $organizer,
        event: $event,
        rating: 4,
    ))->toThrow(
        DomainException::class,
        'You have already reviewed this user for this event.',
    );

    $this->assertDatabaseCount('reviews', 1);
});
