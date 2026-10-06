<?php

use App\Actions\AcceptApplicationAction;
use App\Actions\CreateApplicationAction;
use App\Actions\RejectApplicationAction;
use App\Actions\WaitlistApplicationAction;
use App\Actions\WithdrawApplicationAction;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a verified user to apply to a published event', function () {
    $applicant = User::factory()->create();

    $event = Event::factory()->create();

    $application = app(CreateApplicationAction::class)->execute(
        applicant: $applicant,
        event: $event,
        coverLetter: 'I would like to join this event.',
    );

    expect($application)
        ->applicant_id->toBe($applicant->id)
        ->event_id->toBe($event->id)
        ->cover_letter->toBe('I would like to join this event.')
        ->status->toBe(ApplicationStatus::Pending);

    $this->assertDatabaseHas('applications', [
        'event_id' => $event->id,
        'applicant_id' => $applicant->id,
        'status' => ApplicationStatus::Pending->value,
    ]);
});

it('does not allow an unverified user to apply', function () {
    $applicant = User::factory()->unverified()->create();
    $event = Event::factory()->create();

    expect(fn () => app(CreateApplicationAction::class)->execute(
        applicant: $applicant,
        event: $event,
    ))->toThrow(DomainException::class, 'Email must be verified.');

    $this->assertDatabaseCount('applications', 0);
});

it('does not allow an organizer to apply to their own event', function () {
    $organizer = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    expect(fn () => app(CreateApplicationAction::class)->execute(
        applicant: $organizer,
        event: $event,
    ))->toThrow(DomainException::class, 'You cannot apply to your own event.');

    $this->assertDatabaseCount('applications', 0);
});

it('does not allow a user to apply twice to the same event', function () {
    $applicant = User::factory()->create();
    $event = Event::factory()->create();

    $action = app(CreateApplicationAction::class);

    $action->execute(
        applicant: $applicant,
        event: $event,
    );

    expect(fn () => $action->execute(
        applicant: $applicant,
        event: $event,
    ))->toThrow(DomainException::class, 'You have already applied to this event.');

    $this->assertDatabaseCount('applications', 1);
});

it('does not allow applications to an unpublished event', function () {
    $applicant = User::factory()->create();
    $event = Event::factory()->draft()->create();

    expect(fn () => app(CreateApplicationAction::class)->execute(
        applicant: $applicant,
        event: $event,
    ))->toThrow(
        DomainException::class,
        'Applications are not allowed for this event.',
    );

    $this->assertDatabaseCount('applications', 0);
});

it('allows a pending application to be accepted', function () {
    $event = Event::factory()->create([
        'spots_total' => 5,
        'spots_taken' => 0,
    ]);

    $application = Application::factory()->create([
        'event_id' => $event->id,
        'status' => ApplicationStatus::Pending,
    ]);

    $acceptedApplication = app(AcceptApplicationAction::class)
        ->execute($application);

    expect($acceptedApplication->status)
        ->toBe(ApplicationStatus::Accepted);

    expect($event->refresh()->spots_taken)
        ->toBe(1);

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'status' => ApplicationStatus::Accepted->value,
    ]);
});

it('does not accept an application when the event is full', function () {
    $event = Event::factory()->create([
        'spots_total' => 5,
        'spots_taken' => 5,
    ]);

    $application = Application::factory()->create([
        'event_id' => $event->id,
        'status' => ApplicationStatus::Pending,
    ]);

    expect(fn () => app(AcceptApplicationAction::class)->execute($application))
        ->toThrow(DomainException::class, 'No spots are available.');

    expect($application->refresh()->status)
        ->toBe(ApplicationStatus::Pending);

    expect($event->refresh()->spots_taken)
        ->toBe(5);
});

it('does not accept the same application twice', function () {
    $event = Event::factory()->create([
        'spots_total' => 5,
        'spots_taken' => 0,
    ]);

    $application = Application::factory()->create([
        'event_id' => $event->id,
        'status' => ApplicationStatus::Pending,
    ]);

    $action = app(AcceptApplicationAction::class);

    $action->execute($application);

    expect(fn () => $action->execute($application))
        ->toThrow(
            DomainException::class,
            'This application cannot be accepted.',
        );

    expect($application->refresh()->status)
        ->toBe(ApplicationStatus::Accepted);

    expect($event->refresh()->spots_taken)
        ->toBe(1);
});

it('allows a pending application to be rejected', function () {
    $application = Application::factory()->create([
        'status' => ApplicationStatus::Pending,
    ]);

    $rejected = app(RejectApplicationAction::class)
        ->execute($application);

    expect($rejected->status)
        ->toBe(ApplicationStatus::Rejected);
});

it('allows a pending application to be withdrawn', function () {
    $application = Application::factory()->create([
        'status' => ApplicationStatus::Pending,
    ]);

    $withdrawn = app(WithdrawApplicationAction::class)
        ->execute($application);

    expect($withdrawn->status)
        ->toBe(ApplicationStatus::Withdrawn);
});

it('frees a spot when an accepted application is withdrawn', function () {
    $event = Event::factory()->create([
        'spots_total' => 5,
        'spots_taken' => 1,
    ]);

    $application = Application::factory()->create([
        'event_id' => $event->id,
        'status' => ApplicationStatus::Accepted,
    ]);

    $withdrawn = app(WithdrawApplicationAction::class)
        ->execute($application);

    expect($withdrawn->status)
        ->toBe(ApplicationStatus::Withdrawn);

    expect($event->refresh()->spots_taken)
        ->toBe(0);
});

it('allows a pending application to be waitlisted', function () {
    $application = Application::factory()->create([
        'status' => ApplicationStatus::Pending,
    ]);

    $waitlisted = app(WaitlistApplicationAction::class)
        ->execute($application);

    expect($waitlisted->status)
        ->toBe(ApplicationStatus::Waitlisted);
});

it('does not take a spot when an application is waitlisted', function () {
    $event = Event::factory()->create([
        'spots_total' => 5,
        'spots_taken' => 0,
    ]);

    $application = Application::factory()->create([
        'event_id' => $event->id,
        'status' => ApplicationStatus::Pending,
    ]);

    app(WaitlistApplicationAction::class)->execute($application);

    expect($event->refresh()->spots_taken)
        ->toBe(0);
});

it('allows a waitlisted application to be accepted', function () {
    $event = Event::factory()->create([
        'spots_total' => 5,
        'spots_taken' => 0,
    ]);

    $application = Application::factory()->create([
        'event_id' => $event->id,
        'status' => ApplicationStatus::Waitlisted,
    ]);

    $accepted = app(AcceptApplicationAction::class)
        ->execute($application);

    expect($accepted->status)
        ->toBe(ApplicationStatus::Accepted);

    expect($event->refresh()->spots_taken)
        ->toBe(1);
});

it('allows a rejected application to be accepted', function () {
    $event = Event::factory()->create([
        'spots_total' => 5,
        'spots_taken' => 0,
    ]);

    $application = Application::factory()->create([
        'event_id' => $event->id,
        'status' => ApplicationStatus::Rejected,
    ]);

    $accepted = app(AcceptApplicationAction::class)
        ->execute($application);

    expect($accepted->status)
        ->toBe(ApplicationStatus::Accepted);

    expect($event->refresh()->spots_taken)
        ->toBe(1);
});

it('allows an accepted application to be rejected', function () {
    $event = Event::factory()->create([
        'spots_total' => 5,
        'spots_taken' => 1,
    ]);

    $application = Application::factory()->create([
        'event_id' => $event->id,
        'status' => ApplicationStatus::Accepted,
    ]);

    $rejected = app(RejectApplicationAction::class)
        ->execute($application);

    expect($rejected->status)
        ->toBe(ApplicationStatus::Rejected);

    expect($event->refresh()->spots_taken)
        ->toBe(0);
});

it('does not allow a rejected application to be accepted when the event is full', function () {
    $event = Event::factory()->create([
        'spots_total' => 5,
        'spots_taken' => 5,
    ]);

    $application = Application::factory()->create([
        'event_id' => $event->id,
        'status' => ApplicationStatus::Rejected,
    ]);

    expect(fn () => app(AcceptApplicationAction::class)->execute($application))
        ->toThrow(
            DomainException::class,
            'No spots are available.',
        );

    expect($application->refresh()->status)
        ->toBe(ApplicationStatus::Rejected);

    expect($event->refresh()->spots_taken)
        ->toBe(5);
});

it('does not allow changing an application decision after the event is completed', function () {
    $event = Event::factory()->completed()->create([
        'spots_total' => 5,
        'spots_taken' => 0,
    ]);

    $application = Application::factory()->create([
        'event_id' => $event->id,
        'status' => ApplicationStatus::Rejected,
    ]);

    expect(fn () => app(AcceptApplicationAction::class)->execute($application))
        ->toThrow(
            DomainException::class,
            'Applications for closed events cannot be changed.',
        );

    expect($application->refresh()->status)
        ->toBe(ApplicationStatus::Rejected);
});

it('does not allow changing an application decision after the event is cancelled', function () {
    $event = Event::factory()->cancelled()->create([
        'spots_total' => 5,
        'spots_taken' => 0,
    ]);

    $application = Application::factory()->create([
        'event_id' => $event->id,
        'status' => ApplicationStatus::Pending,
    ]);

    expect(fn () => app(RejectApplicationAction::class)->execute($application))
        ->toThrow(
            DomainException::class,
            'Applications for closed events cannot be changed.',
        );

    expect($application->refresh()->status)
        ->toBe(ApplicationStatus::Pending);
});
