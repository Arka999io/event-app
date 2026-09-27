<?php

use App\Actions\BanUserAction;
use App\Actions\UnbanUserAction;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;

it('bans a user', function () {
    $user = User::factory()->create();

    app(BanUserAction::class)->execute(
        user: $user,
        reason: 'Violation of platform rules.',
    );

    $user->refresh();

    expect($user->is_banned)
        ->toBeTrue()
        ->and($user->banned_at)
        ->not->toBeNull()
        ->and($user->ban_reason)
        ->toBe('Violation of platform rules.');
});

it('cancels active events when a user is banned', function () {
    $user = User::factory()->create();

    $draft = Event::factory()->create([
        'organizer_id' => $user->id,
        'status' => EventStatus::Draft,
    ]);

    $published = Event::factory()->create([
        'organizer_id' => $user->id,
        'status' => EventStatus::Published,
    ]);

    $ongoing = Event::factory()->create([
        'organizer_id' => $user->id,
        'status' => EventStatus::Ongoing,
    ]);

    app(BanUserAction::class)->execute(
        user: $user,
        reason: 'Violation of platform rules.',
    );

    expect($draft->refresh()->status)
        ->toBe(EventStatus::Cancelled)
        ->and($published->refresh()->status)
        ->toBe(EventStatus::Cancelled)
        ->and($ongoing->refresh()->status)
        ->toBe(EventStatus::Cancelled);
});

it('does not change completed events when a user is banned', function () {
    $user = User::factory()->create();

    $completed = Event::factory()->create([
        'organizer_id' => $user->id,
        'status' => EventStatus::Completed,
    ]);

    $cancelled = Event::factory()->create([
        'organizer_id' => $user->id,
        'status' => EventStatus::Cancelled,
    ]);

    app(BanUserAction::class)->execute(
        user: $user,
        reason: 'Violation of platform rules.',
    );

    expect($completed->refresh()->status)
        ->toBe(EventStatus::Completed)
        ->and($cancelled->refresh()->status)
        ->toBe(EventStatus::Cancelled);
});

it('does not allow banning the same user twice', function () {
    $user = User::factory()->create();

    $action = app(BanUserAction::class);

    $action->execute(
        user: $user,
        reason: 'Violation of platform rules.',
    );

    expect(
        fn () => $action->execute(
            user: $user->refresh(),
            reason: 'Another reason.',
        )
    )->toThrow(
        \DomainException::class,
        'This user is already banned.'
    );
});

it('unbans a user', function () {
    $user = User::factory()->create();

    app(BanUserAction::class)->execute(
        user: $user,
        reason: 'Violation of platform rules.',
    );

    app(UnbanUserAction::class)->execute(
        $user->refresh()
    );

    $user->refresh();

    expect($user->is_banned)
        ->toBeFalse()
        ->and($user->banned_at)
        ->toBeNull()
        ->and($user->ban_reason)
        ->toBeNull();
});

it('does not restore cancelled events after unban', function () {
    $user = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $user->id,
        'status' => EventStatus::Published,
    ]);

    app(BanUserAction::class)->execute(
        user: $user,
        reason: 'Violation of platform rules.',
    );

    app(UnbanUserAction::class)->execute(
        $user->refresh()
    );

    expect($event->refresh()->status)
        ->toBe(EventStatus::Cancelled);
});

it('does not allow unbanning a user who is not banned', function () {
    $user = User::factory()->create();

    expect(
        fn () => app(UnbanUserAction::class)
            ->execute($user)
    )->toThrow(
        \DomainException::class,
        'This user is not banned.'
    );
});