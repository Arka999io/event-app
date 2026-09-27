<?php

namespace App\Actions;

use App\Enums\ApplicationStatus;
use App\Enums\EventStatus;
use App\Models\Application;
use App\Models\Event;
use App\Models\Review;
use App\Models\User;
use DomainException;

class CreateReviewAction
{
    public function execute(
        User $author,
        User $subject,
        Event $event,
        int $rating,
        ?string $comment = null,
    ): Review {
        if ($event->status !== EventStatus::Completed) {
            throw new DomainException(
                'Reviews can only be created for completed events.'
            );
        }

        if ($author->id === $subject->id) {
            throw new DomainException(
                'You cannot review yourself.'
            );
        }

        if ($rating < 1 || $rating > 5) {
            throw new DomainException(
                'Rating must be between 1 and 5.'
            );
        }

        $organizerId = $event->organizer_id;

        $authorIsOrganizer = $author->id === $organizerId;
        $subjectIsOrganizer = $subject->id === $organizerId;

        if ($authorIsOrganizer === $subjectIsOrganizer) {
            throw new DomainException(
                'Reviews are only allowed between an organizer and an accepted participant.'
            );
        }

        $participant = $authorIsOrganizer ? $subject : $author;

        $isAcceptedParticipant = Application::query()
            ->where('event_id', $event->id)
            ->where('applicant_id', $participant->id)
            ->where('status', ApplicationStatus::Accepted->value)
            ->exists();

        if (! $isAcceptedParticipant) {
            throw new DomainException(
                'The participant must have an accepted application.'
            );
        }

        if (Review::query()
            ->where('event_id', $event->id)
            ->where('author_id', $author->id)
            ->where('subject_id', $subject->id)
            ->exists()) {
            throw new DomainException(
                'You have already reviewed this user for this event.'
            );
        }

        return Review::query()->create([
            'event_id' => $event->id,
            'author_id' => $author->id,
            'subject_id' => $subject->id,
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }
}