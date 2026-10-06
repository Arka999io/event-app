<?php

namespace App\Actions;

use App\Models\Block;
use App\Models\User;
use DomainException;

class UnblockUserAction
{
    public function execute(
        User $blocker,
        User $blocked,
    ): void {
        if ($blocker->id === $blocked->id) {
            throw new DomainException(
                'You cannot unblock yourself.'
            );
        }

        $block = Block::query()
            ->where('blocker_id', $blocker->id)
            ->where('blocked_id', $blocked->id)
            ->first();

        if ($block === null) {
            throw new DomainException(
                'You have not blocked this user.'
            );
        }

        $block->delete();
    }
}
