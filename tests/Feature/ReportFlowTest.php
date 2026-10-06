<?php

use App\Actions\CreateReportAction;
use App\Actions\SendMessageAction;
use App\Actions\StartConversationAction;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a user to report another user', function () {
    $reporter = User::factory()->create();
    $reportedUser = User::factory()->create();

    $report = app(CreateReportAction::class)->execute(
        reporter: $reporter,
        reportable: $reportedUser,
        reason: ReportReason::Harassment,
        description: 'Inappropriate behaviour.',
    );

    expect($report)
        ->reporter_id->toBe($reporter->id)
        ->reason->toBe(ReportReason::Harassment)
        ->status->toBe(ReportStatus::Pending);

    expect($report->reportable)
        ->toBeInstanceOf(User::class)
        ->id->toBe($reportedUser->id);
});

it('allows a user to report an event', function () {
    $reporter = User::factory()->create();
    $event = Event::factory()->create();

    $report = app(CreateReportAction::class)->execute(
        reporter: $reporter,
        reportable: $event,
        reason: ReportReason::Spam,
    );

    expect($report)
        ->reason->toBe(ReportReason::Spam)
        ->status->toBe(ReportStatus::Pending);

    expect($report->reportable)
        ->toBeInstanceOf(Event::class)
        ->id->toBe($event->id);
});

it('does not allow a user to report themselves', function () {
    $user = User::factory()->create();

    expect(fn () => app(CreateReportAction::class)->execute(
        reporter: $user,
        reportable: $user,
        reason: ReportReason::Other,
    ))->toThrow(
        DomainException::class,
        'You cannot report yourself.',
    );

    $this->assertDatabaseCount('reports', 0);
});

it('allows a conversation participant to report a message', function () {
    $organizer = User::factory()->create();
    $user = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    $conversation = app(StartConversationAction::class)->execute(
        user: $user,
        event: $event,
    );

    $message = app(SendMessageAction::class)->execute(
        sender: $organizer,
        conversation: $conversation,
        body: 'Test message',
    );

    $report = app(CreateReportAction::class)->execute(
        reporter: $user,
        reportable: $message,
        reason: ReportReason::Harassment,
    );

    expect($report->status)->toBe(ReportStatus::Pending);

    $this->assertDatabaseHas('reports', [
        'reporter_id' => $user->id,
        'reportable_type' => $message->getMorphClass(),
        'reportable_id' => $message->id,
        'status' => ReportStatus::Pending->value,
    ]);
});

it('does not allow a stranger to report a message from another conversation', function () {
    $organizer = User::factory()->create();
    $user = User::factory()->create();
    $stranger = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    $conversation = app(StartConversationAction::class)->execute(
        user: $user,
        event: $event,
    );

    $message = app(SendMessageAction::class)->execute(
        sender: $organizer,
        conversation: $conversation,
        body: 'Test message',
    );

    expect(fn () => app(CreateReportAction::class)->execute(
        reporter: $stranger,
        reportable: $message,
        reason: ReportReason::Harassment,
    ))->toThrow(
        DomainException::class,
        'You cannot report a message from this conversation.',
    );

    $this->assertDatabaseCount('reports', 0);
});

it('does not allow duplicate active reports for the same content', function () {
    $reporter = User::factory()->create();
    $reportedUser = User::factory()->create();

    $action = app(CreateReportAction::class);

    $action->execute(
        reporter: $reporter,
        reportable: $reportedUser,
        reason: ReportReason::Spam,
    );

    expect(fn () => $action->execute(
        reporter: $reporter,
        reportable: $reportedUser,
        reason: ReportReason::Other,
    ))->toThrow(
        DomainException::class,
        'You already have an active report for this content.',
    );

    $this->assertDatabaseCount('reports', 1);
});
