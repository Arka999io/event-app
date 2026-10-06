<?php

namespace App\Policies;

use App\Actions\UsersAreBlockedAction;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    public function __construct(
        private UsersAreBlockedAction $usersAreBlocked,
    ) {}

    public function view(?User $user, Event $event): Response|bool
    {
        if ($user !== null && $user->id === $event->organizer_id) {
            return true;
        }

        // Черновик видит только организатор. 404, чтобы не раскрывать, что такой ID существует.
        if ($event->status === EventStatus::Draft) {
            return Response::denyAsNotFound();
        }

        // Гости могут смотреть опубликованные, идущие, завершённые и отменённые события.
        if ($user === null) {
            return true;
        }

        return ! $this->usersAreBlocked->execute($user, $event->organizer);
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
                    EventStatus::Completed,
                    EventStatus::Cancelled,
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
