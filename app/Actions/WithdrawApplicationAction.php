<?php

namespace App\Actions;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Event;
use DomainException;
use Illuminate\Support\Facades\DB;

class WithdrawApplicationAction
{
    public function execute(Application $application): Application
    {
        return DB::transaction(function () use ($application): Application {
            /** @var Application $lockedApplication */
            $lockedApplication = Application::query()
                ->lockForUpdate()
                ->findOrFail($application->id);

            if (! in_array($lockedApplication->status, [
                ApplicationStatus::Pending,
                ApplicationStatus::Accepted,
                ApplicationStatus::Waitlisted,
            ], true)) {
                throw new DomainException(
                    'This application cannot be withdrawn.'
                );
            }

            if ($lockedApplication->status === ApplicationStatus::Accepted) {
                /** @var Event $event */
                $event = Event::query()
                    ->lockForUpdate()
                    ->findOrFail($lockedApplication->event_id);

                if ($event->spots_taken <= 0) {
                    throw new DomainException(
                        'Event spots count is inconsistent.'
                    );
                }

                $event->decrement('spots_taken');
            }

            $lockedApplication->update([
                'status' => ApplicationStatus::Withdrawn,
            ]);

            return $lockedApplication->refresh();
        });
    }
}
