<?php

namespace App\Actions;

use App\Enums\EventStatus;
use App\Models\Event;
use DomainException;

class PublishEventAction
{
    public function execute(Event $event): Event
    {
        if ($event->status !== EventStatus::Draft) {
            throw new DomainException(
                'Only draft events can be published.'
            );
        }

        if ($event->starts_at->isPast()) {
            throw new DomainException(
                'An event with a past start time cannot be published.'
            );
        }

        $event->update([
            'status' => EventStatus::Published,
            'published_at' => now(),
        ]);

        return $event->refresh();
    }
}
