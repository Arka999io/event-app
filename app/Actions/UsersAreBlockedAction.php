<?php

namespace App\Actions;

use App\Models\Block;
use App\Models\User;

class UsersAreBlockedAction
{
    public function execute(User $first, User $second): bool
    {
        return Block::query()
            ->where(function ($query) use ($first, $second) {
                $query
                    ->where('blocker_id', $first->id)
                    ->where('blocked_id', $second->id);
            })
            ->orWhere(function ($query) use ($first, $second) {
                $query
                    ->where('blocker_id', $second->id)
                    ->where('blocked_id', $first->id);
            })
            ->exists();
    }
}
