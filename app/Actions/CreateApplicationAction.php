<?php

namespace App\Actions;

use App\Enums\ApplicationStatus;
use App\Enums\EventStatus;
use App\Models\Application;
use App\Models\Event;
use App\Models\User;
use App\Notifications\NewApplicationNotification;
use DomainException;

class CreateApplicationAction
{
    public function __construct(
        private UsersAreBlockedAction $usersAreBlocked,
    ) {}

    public function execute(
        User $applicant,
        Event $event,
        ?string $coverLetter = null,
    ): Application {
        if (! $applicant->hasVerifiedEmail()) {
            throw new DomainException('Email must be verified.');
        }

        if ($event->organizer_id === $applicant->id) {
            throw new DomainException('You cannot apply to your own event.');
        }

        if ($this->usersAreBlocked->execute($applicant, $event->organizer)) {
            throw new DomainException('You cannot apply to this event.');
        }

        if ($event->status !== EventStatus::Published) {
            throw new DomainException('Applications are not allowed for this event.');
        }

        if ($event->applications()->where('applicant_id', $applicant->id)->exists()) {
            throw new DomainException('You have already applied to this event.');
        }

        $application = $event->applications()->create([
            'applicant_id' => $applicant->id,
            'cover_letter' => $coverLetter,
            'status' => ApplicationStatus::Pending,
        ]);

        $application->load([
            'event.organizer',
            'applicant',
        ]);

        $event->organizer->notify(
            new NewApplicationNotification($application)
        );

        return $application;
    }
}
