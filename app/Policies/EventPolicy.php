<?php

namespace App\Policies;

use App\Actions\UsersAreBlockedAction;
use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function __construct(
        private UsersAreBlockedAction $usersAreBlocked,
    ) {}

    public function view(User $user, Event $event): bool
    {
        if ($user->id === $event->organizer_id) {
            return true;
        }

        $organizer = $event->organizer;

        return ! $this->usersAreBlocked->execute(
            $user,
            $organizer,
        );
    }

    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    public function update(User $user, Event $event): bool
    {
        return $user->id === $event->organizer_id;
    }

    public function publish(User $user, Event $event): bool
    {
        return $user->id === $event->organizer_id;
    }

    public function cancel(User $user, Event $event): bool
    {
        return $user->id === $event->organizer_id
            && ! in_array(
                $event->status,
                [
                    \App\Enums\EventStatus::Completed,
                    \App\Enums\EventStatus::Cancelled,
                ],
                true
            );
    }

    public function complete(User $user, Event $event): bool
    {
        return $user->id === $event->organizer_id;
    }

    public function start(User $user, Event $event): bool
    {
        return $user->id === $event->organizer_id;
    }
}