<?php

namespace App\Actions;

use App\Enums\EventStatus;
use App\Models\Event;
use DomainException;

class CompleteEventAction
{
    public function execute(Event $event): Event
    {
        if (
            ! in_array(
                $event->status,
                [
                    EventStatus::Published,
                    EventStatus::Ongoing,
                ],
                true
            )
        ) {
            throw new DomainException(
                'This event cannot be completed.'
            );
        }

        if ($event->starts_at->isFuture()) {
            throw new DomainException(
                'An event cannot be completed before it starts.'
            );
        }

        $event->update([
            'status' => EventStatus::Completed,
        ]);

        return $event->refresh();
    }
}
