<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Message $message,
        private readonly Conversation $conversation,
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
            'type' => 'new_message',
            'message_id' => $this->message->id,
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'event_id' => $this->conversation->event_id,
            'event_title' => $this->conversation->event->title,
            'text' => 'Новое сообщение от '.$this->message->sender->name,
        ];
    }
}
