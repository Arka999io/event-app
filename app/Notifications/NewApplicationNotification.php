<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewApplicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Application $application,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'new_application',
            'application_id' => $this->application->id,
            'event_id' => $this->application->event_id,
            'event_title' => $this->application->event->title,
            'applicant_id' => $this->application->applicant_id,
            'applicant_name' => $this->application->applicant->name,
            'text' => $this->application->applicant->name
                .' откликнулся на ваше мероприятие.',
        ];
    }
}
