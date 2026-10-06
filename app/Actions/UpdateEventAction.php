<?php

namespace App\Actions;

use App\DTO\CreateEventDTO;
use App\Enums\EventStatus;
use App\Models\Event;
use DomainException;

class UpdateEventAction
{
    public function execute(
        Event $event,
        CreateEventDTO $data,
    ): Event {
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
                'This event cannot be edited.'
            );
        }

        if ($data->spotsTotal < $event->spots_taken) {
            throw new DomainException(
                'Event capacity cannot be lower than the number of accepted participants.'
            );
        }

        $event->update([
            'category_id' => $data->categoryId,
            'title' => $data->title,
            'description' => $data->description,
            'starts_at' => $data->startsAt,
            'ends_at' => $data->endsAt,
            'meeting_address' => $data->meetingAddress,
            'venue_address' => $data->venueAddress,
            'min_age' => $data->minAge,
            'max_age' => $data->maxAge,
            'gender_preference' => $data->genderPreference,
            'spots_total' => $data->spotsTotal,
        ]);

        return $event->refresh();
    }
}
