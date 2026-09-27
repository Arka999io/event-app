<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function view(User $user, Application $application): bool
    {
        return $user->id === $application->applicant_id
            || $user->id === $application->event->organizer_id;
    }

    public function accept(User $user, Application $application): bool
    {
        return $user->id === $application->event->organizer_id;
    }

    public function reject(User $user, Application $application): bool
    {
        return $user->id === $application->event->organizer_id;
    }

    public function withdraw(User $user, Application $application): bool
    {
        return $user->id === $application->applicant_id;
    }

    public function waitlist(User $user, Application $application): bool
    {
        return $user->id === $application->event->organizer_id;
    }
}