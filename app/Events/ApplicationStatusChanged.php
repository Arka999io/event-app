<?php

namespace App\Events;

use App\Models\Application;
use App\Models\Conversation;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Application $application,
        public Conversation $conversation,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'conversation.'.$this->conversation->id
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'application.status.changed';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'application_id' => $this->application->id,
            'status' => $this->application->status->value,
        ];
    }
}
