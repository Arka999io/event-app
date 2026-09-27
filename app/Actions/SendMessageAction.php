<?php

namespace App\Actions;

use App\Enums\EventStatus;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\Message;
use App\Models\User;
use DomainException;
use App\Events\MessageSent;
use App\Notifications\NewMessageNotification;

class SendMessageAction
{
    public function __construct(
        private UsersAreBlockedAction $usersAreBlocked,
    ) {}

    public function execute(
        User $sender,
        Conversation $conversation,
        string $body,
    ): Message {
        /** @var Event $event */
        $event = Event::query()->findOrFail($conversation->event_id);

        if (
            $sender->id !== $conversation->user_id
            && $sender->id !== $event->organizer_id
        ) {
            throw new DomainException(
                'You are not a participant of this conversation.'
            );
        }

        /** @var User $user */
        $user = User::query()->findOrFail($conversation->user_id);

        /** @var User $organizer */
        $organizer = User::query()->findOrFail($event->organizer_id);

        if ($this->usersAreBlocked->execute($user, $organizer)) {
            throw new DomainException(
                'This conversation is blocked.'
            );
        }

        if (in_array($event->status, [
            EventStatus::Completed,
            EventStatus::Cancelled,
        ], true)) {
            throw new DomainException(
                'This conversation is read-only.'
            );
        }

        $body = trim($body);

        if ($body === '') {
            throw new DomainException(
                'Message cannot be empty.'
            );
        }

        $message = $conversation->messages()->create([
        'sender_id' => $sender->id,
        'body' => $body,
    ]);

    $conversation->touch();

    $message->load('sender');

    $conversation->load('event');

    $recipient = $sender->id === $conversation->user_id
        ? $organizer
        : $user;

    $recipient->notify(
        new NewMessageNotification(
            message: $message,
            conversation: $conversation,
        )
    );

    MessageSent::dispatch($message);

    return $message;
    }
}