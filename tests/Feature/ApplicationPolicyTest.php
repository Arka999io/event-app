<?php

use App\Models\Application;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows the applicant to view their application', function () {
    $applicant = User::factory()->create();

    $application = Application::factory()->create([
        'applicant_id' => $applicant->id,
    ]);

    expect($applicant->can('view', $application))->toBeTrue();
});

it('allows the event organizer to view an application', function () {
    $organizer = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    $application = Application::factory()->create([
        'event_id' => $event->id,
    ]);

    expect($organizer->can('view', $application))->toBeTrue();
});

it('does not allow a stranger to view an application', function () {
    $stranger = User::factory()->create();
    $application = Application::factory()->create();

    expect($stranger->can('view', $application))->toBeFalse();
});

it('allows only the organizer to accept an application', function () {
    $organizer = User::factory()->create();
    $applicant = User::factory()->create();
    $stranger = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    $application = Application::factory()->create([
        'event_id' => $event->id,
        'applicant_id' => $applicant->id,
    ]);

    expect($organizer->can('accept', $application))->toBeTrue()
        ->and($applicant->can('accept', $application))->toBeFalse()
        ->and($stranger->can('accept', $application))->toBeFalse();
});

it('allows only the organizer to reject an application', function () {
    $organizer = User::factory()->create();
    $applicant = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    $application = Application::factory()->create([
        'event_id' => $event->id,
        'applicant_id' => $applicant->id,
    ]);

    expect($organizer->can('reject', $application))->toBeTrue()
        ->and($applicant->can('reject', $application))->toBeFalse();
});

it('allows only the applicant to withdraw their application', function () {
    $organizer = User::factory()->create();
    $applicant = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    $application = Application::factory()->create([
        'event_id' => $event->id,
        'applicant_id' => $applicant->id,
    ]);

    expect($applicant->can('withdraw', $application))->toBeTrue()
        ->and($organizer->can('withdraw', $application))->toBeFalse();
});