<?php

namespace App\Actions;

use App\Enums\EventStatus;
use App\Models\Event;
use DomainException;

class CancelEventAction
{
    public function execute(Event $event): Event
    {
        if (
            in_array(
                $event->status,
                [
                    EventStatus::Completed,
                    EventStatus::Cancelled,
                ],
                true
            )
        ) {
            throw new DomainException(
                'This event cannot be cancelled.'
            );
        }

        $event->update([
            'status' => EventStatus::Cancelled,
        ]);

        return $event->refresh();
    }
}