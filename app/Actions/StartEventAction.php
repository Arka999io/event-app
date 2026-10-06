<?php

namespace App\Actions;

use App\Enums\EventStatus;
use App\Models\Event;
use DomainException;

class StartEventAction
{
    public function execute(Event $event): Event
    {
        if ($event->status !== EventStatus::Published) {
            throw new DomainException(
                'Only published events can be started.'
            );
        }

        if ($event->starts_at->isFuture()) {
            throw new DomainException(
                'An event cannot be started before its start time.'
            );
        }

        $event->update([
            'status' => EventStatus::Ongoing,
        ]);

        return $event->refresh();
    }
}
