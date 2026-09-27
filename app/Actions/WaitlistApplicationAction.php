<?php

namespace App\Actions;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use DomainException;

class WaitlistApplicationAction
{
    public function execute(Application $application): Application
    {
        if ($application->status !== ApplicationStatus::Pending) {
            throw new DomainException(
                'Only pending applications can be waitlisted.'
            );
        }

        $application->update([
            'status' => ApplicationStatus::Waitlisted,
        ]);

        return $application->refresh();
    }
}