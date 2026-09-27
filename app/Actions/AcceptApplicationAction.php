<?php

namespace App\Actions;

use App\Enums\ApplicationStatus;
use App\Enums\EventStatus;
use App\Models\Application;
use App\Models\Event;
use DomainException;
use Illuminate\Support\Facades\DB;
use App\Notifications\ApplicationAcceptedNotification;

class AcceptApplicationAction
{
    public function execute(Application $application): Application
    {
        return DB::transaction(function () use ($application): Application {
            /** @var Application $lockedApplication */
            $lockedApplication = Application::query()
                ->lockForUpdate()
                ->findOrFail($application->id);

            /** @var Event $event */
            $event = Event::query()
                ->lockForUpdate()
                ->findOrFail($lockedApplication->event_id);

            if (in_array($event->status, [
                EventStatus::Completed,
                EventStatus::Cancelled,
            ], true)) {
                throw new DomainException(
                    'Applications for closed events cannot be changed.'
                );
            }

            if (! in_array($lockedApplication->status, [
                ApplicationStatus::Pending,
                ApplicationStatus::Waitlisted,
                ApplicationStatus::Rejected,
            ], true)) {
                throw new DomainException(
                    'This application cannot be accepted.'
                );
            }

            if ($event->spots_taken >= $event->spots_total) {
                throw new DomainException(
                    'No spots are available.'
                );
            }

            $lockedApplication->update([
                'status' => ApplicationStatus::Accepted,
            ]);

            $event->increment('spots_taken');

            $lockedApplication = $lockedApplication->refresh();

            $lockedApplication->load([
                'event',
                'applicant',
            ]);

            $lockedApplication->applicant->notify(
                new ApplicationAcceptedNotification(
                    $lockedApplication
                )
            );

            return $lockedApplication;
        });
    }
}