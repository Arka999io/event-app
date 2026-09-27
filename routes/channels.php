<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(
    'conversation.{conversationId}',
    function (User $user, int $conversationId): bool {
        $conversation = Conversation::query()
            ->with('event')
            ->find($conversationId);

        if ($conversation === null) {
            return false;
        }

        return $user->id === $conversation->user_id
            || $user->id === $conversation->event->organizer_id;
    }
);