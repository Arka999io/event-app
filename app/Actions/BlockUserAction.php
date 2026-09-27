<?php

namespace App\Actions;

use App\Models\Block;
use App\Models\User;
use DomainException;

class BlockUserAction
{
    public function execute(User $blocker, User $blocked): Block
    {
        if ($blocker->id === $blocked->id) {
            throw new DomainException('You cannot block yourself.');
        }

        return Block::query()->firstOrCreate([
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked->id,
        ]);
    }
}