<?php

namespace App\Actions;

use App\Models\Conversation;
use App\Models\User;

class MarkConversationAsReadAction
{
    public function execute(
        User $user,
        Conversation $conversation,
    ): void {
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);
    }
}
