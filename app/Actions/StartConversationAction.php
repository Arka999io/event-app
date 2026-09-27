<?php

namespace App\Actions;

use App\Enums\EventStatus;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\User;
use DomainException;

class StartConversationAction
{
    public function __construct(
        private UsersAreBlockedAction $usersAreBlocked,
    ) {}

    public function execute(User $user, Event $event): Conversation
    {
        if ($event->organizer_id === $user->id) {
            throw new DomainException(
                'You cannot start a conversation with yourself.'
            );
        }

        $organizer = $event->organizer;

        if ($this->usersAreBlocked->execute($user, $organizer)) {
            throw new DomainException(
                'You cannot start a conversation with this user.'
            );
        }

        if (in_array($event->status, [
            EventStatus::Completed,
            EventStatus::Cancelled,
        ], true)) {
            throw new DomainException(
                'You cannot start a conversation for a closed event.'
            );
        }

        return Conversation::query()->firstOrCreate([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);
    }
}