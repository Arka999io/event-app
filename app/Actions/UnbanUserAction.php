<?php

namespace App\Actions;

use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class UnbanUserAction
{
    public function execute(User $user): User
    {
        if (! $user->is_banned) {
            throw new DomainException(
                'This user is not banned.'
            );
        }

        return DB::transaction(
            function () use ($user): User {
                $user->forceFill([
                    'is_banned' => false,
                    'banned_at' => null,
                    'ban_reason' => null,
                ])->save();

                return $user->refresh();
            }
        );
    }
}