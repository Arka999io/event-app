<?php

use App\Actions\BlockUserAction;
use App\Actions\MarkConversationAsReadAction;
use App\Actions\SendMessageAction;
use App\Actions\StartConversationAction;
use App\Enums\EventStatus;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a user to start a conversation without an application', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $conversation = app(StartConversationAction::class)->execute(
        user: $user,
        event: $event,
    );

    expect($conversation)
        ->toBeInstanceOf(Conversation::class)
        ->event_id->toBe($event->id)
        ->user_id->toBe($user->id);

    $this->assertDatabaseHas('conversations', [
        'event_id' => $event->id,
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseCount('applications', 0);
});

it('returns the existing conversation instead of creating a duplicate', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $action = app(StartConversationAction::class);

    $first = $action->execute($user, $event);
    $second = $action->execute($user, $event);

    expect($second->id)->toBe($first->id);

    $this->assertDatabaseCount('conversations', 1);
});

it('does not allow an organizer to start a conversation with themselves', function () {
    $organizer = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    expect(fn () => app(StartConversationAction::class)->execute(
        user: $organizer,
        event: $event,
    ))->toThrow(
        DomainException::class,
        'You cannot start a conversation with yourself.',
    );

    $this->assertDatabaseCount('conversations', 0);
});

it('does not allow starting a conversation for a completed event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->completed()->create();

    expect(fn () => app(StartConversationAction::class)->execute(
        user: $user,
        event: $event,
    ))->toThrow(
        DomainException::class,
        'You cannot start a conversation for a closed event.',
    );

    $this->assertDatabaseCount('conversations', 0);
});

it('does not allow starting a conversation for a cancelled event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->cancelled()->create();

    expect(fn () => app(StartConversationAction::class)->execute(
        user: $user,
        event: $event,
    ))->toThrow(
        DomainException::class,
        'You cannot start a conversation for a closed event.',
    );

    $this->assertDatabaseCount('conversations', 0);
});

it('allows the user to send a message to the organizer', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $conversation = app(StartConversationAction::class)->execute(
        user: $user,
        event: $event,
    );

    $message = app(SendMessageAction::class)->execute(
        sender: $user,
        conversation: $conversation,
        body: 'Where exactly are we meeting?',
    );

    expect($message)
        ->sender_id->toBe($user->id)
        ->conversation_id->toBe($conversation->id)
        ->body->toBe('Where exactly are we meeting?');

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'sender_id' => $user->id,
        'body' => 'Where exactly are we meeting?',
    ]);
});

it('allows the organizer to reply in a conversation', function () {
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
        body: 'We are meeting at the main entrance.',
    );

    expect($message)
        ->sender_id->toBe($organizer->id)
        ->body->toBe('We are meeting at the main entrance.');
});

it('does not allow a stranger to send a message', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();
    $event = Event::factory()->create();

    $conversation = app(StartConversationAction::class)->execute(
        user: $user,
        event: $event,
    );

    expect(fn () => app(SendMessageAction::class)->execute(
        sender: $stranger,
        conversation: $conversation,
        body: 'Hello',
    ))->toThrow(
        DomainException::class,
        'You are not a participant of this conversation.',
    );

    $this->assertDatabaseCount('messages', 0);
});

it('does not allow sending an empty message', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $conversation = app(StartConversationAction::class)->execute(
        user: $user,
        event: $event,
    );

    expect(fn () => app(SendMessageAction::class)->execute(
        sender: $user,
        conversation: $conversation,
        body: '   ',
    ))->toThrow(
        DomainException::class,
        'Message cannot be empty.',
    );

    $this->assertDatabaseCount('messages', 0);
});

it('makes the conversation read-only when the event is completed', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $conversation = app(StartConversationAction::class)->execute(
        user: $user,
        event: $event,
    );

    app(SendMessageAction::class)->execute(
        sender: $user,
        conversation: $conversation,
        body: 'See you there!',
    );

    $event->update([
        'status' => EventStatus::Completed,
    ]);

    expect(fn () => app(SendMessageAction::class)->execute(
        sender: $user,
        conversation: $conversation,
        body: 'Can I still write here?',
    ))->toThrow(
        DomainException::class,
        'This conversation is read-only.',
    );

    $this->assertDatabaseCount('conversations', 1);
    $this->assertDatabaseCount('messages', 1);

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'body' => 'See you there!',
    ]);
});

it('makes the conversation read-only when the event is cancelled', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $conversation = app(StartConversationAction::class)->execute(
        user: $user,
        event: $event,
    );

    $event->update([
        'status' => EventStatus::Cancelled,
    ]);

    expect(fn () => app(SendMessageAction::class)->execute(
        sender: $user,
        conversation: $conversation,
        body: 'Hello?',
    ))->toThrow(
        DomainException::class,
        'This conversation is read-only.',
    );

    $this->assertDatabaseCount('conversations', 1);
    $this->assertDatabaseCount('messages', 0);
});

it('does not allow messaging when the user blocked the organizer', function () {
    $organizer = User::factory()->create();
    $user = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    $conversation = app(StartConversationAction::class)->execute(
        $user,
        $event,
    );

    app(BlockUserAction::class)->execute(
        blocker: $user,
        blocked: $organizer,
    );

    expect(fn () => app(SendMessageAction::class)->execute(
        sender: $user,
        conversation: $conversation,
        body: 'Hello',
    ))->toThrow(
        DomainException::class,
        'This conversation is blocked.',
    );

    $this->assertDatabaseCount('messages', 0);
});

it('does not allow messaging when the organizer blocked the user', function () {
    $organizer = User::factory()->create();
    $user = User::factory()->create();

    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
    ]);

    $conversation = app(StartConversationAction::class)->execute(
        $user,
        $event,
    );

    app(BlockUserAction::class)->execute(
        blocker: $organizer,
        blocked: $user,
    );

    expect(fn () => app(SendMessageAction::class)->execute(
        sender: $organizer,
        conversation: $conversation,
        body: 'Hello',
    ))->toThrow(
        DomainException::class,
        'This conversation is blocked.',
    );

    $this->assertDatabaseCount('messages', 0);
});

it('marks incoming messages as read', function () {
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
        body: 'Hello!',
    );

    expect($message->read_at)->toBeNull();

    app(MarkConversationAsReadAction::class)->execute(
        user: $user,
        conversation: $conversation,
    );

    expect($message->refresh()->read_at)
        ->not->toBeNull();
});

it('does not mark the users own messages as read', function () {
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
        sender: $user,
        conversation: $conversation,
        body: 'Hello!',
    );

    app(MarkConversationAsReadAction::class)->execute(
        user: $user,
        conversation: $conversation,
    );

    expect($message->refresh()->read_at)
        ->toBeNull();
});
