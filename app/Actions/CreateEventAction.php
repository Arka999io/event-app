<?php

namespace App\Actions;

use App\DTO\CreateEventDTO;
use App\Enums\EventStatus;
use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use DomainException;

class CreateEventAction
{
    public function execute(
        User $organizer,
        CreateEventDTO $data,
    ): Event {
        if (! $organizer->hasVerifiedEmail()) {
            throw new DomainException(
                'Email must be verified.'
            );
        }

        $category = Category::query()->findOrFail($data->categoryId);

        if (! $category->is_active) {
            throw new DomainException(
                'The selected category is inactive.'
            );
        }

        if ($data->startsAt->isPast()) {
            throw new DomainException(
                'Event start time must be in the future.'
            );
        }

        if (
            $data->endsAt !== null
            && $data->endsAt->lessThanOrEqualTo($data->startsAt)
        ) {
            throw new DomainException(
                'Event end time must be after the start time.'
            );
        }

        if (
            $data->minAge !== null
            && $data->maxAge !== null
            && $data->minAge > $data->maxAge
        ) {
            throw new DomainException(
                'Minimum age cannot be greater than maximum age.'
            );
        }

        if ($data->spotsTotal < 1) {
            throw new DomainException(
                'Event must have at least one spot.'
            );
        }

        return Event::query()->create([
            'organizer_id' => $organizer->id,
            'category_id' => $category->id,
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
            'spots_taken' => 0,
            'status' => EventStatus::Draft,
            'published_at' => null,
        ]);
    }
}
