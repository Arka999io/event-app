<?php

namespace App\Actions;

use App\Enums\ApplicationStatus;
use App\Enums\EventStatus;
use App\Models\Application;
use App\Models\Event;
use App\Notifications\ApplicationRejectedNotification;
use DomainException;
use Illuminate\Support\Facades\DB;

class RejectApplicationAction
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
                ApplicationStatus::Accepted,
            ], true)) {
                throw new DomainException(
                    'This application cannot be rejected.'
                );
            }

            if ($lockedApplication->status === ApplicationStatus::Accepted) {
                if ($event->spots_taken <= 0) {
                    throw new DomainException(
                        'Event spots count is inconsistent.'
                    );
                }

                $event->decrement('spots_taken');
            }

            $lockedApplication->update([
                'status' => ApplicationStatus::Rejected,
            ]);

            $lockedApplication = $lockedApplication->refresh();

            $lockedApplication->load([
                'event',
                'applicant',
            ]);

            $lockedApplication->applicant->notify(
                new ApplicationRejectedNotification(
                    $lockedApplication
                )
            );

            return $lockedApplication;
        });
    }
}
