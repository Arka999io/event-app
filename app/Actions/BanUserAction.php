<?php

namespace App\Actions;

use App\Enums\EventStatus;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class BanUserAction
{
    public function execute(
        User $user,
        string $reason,
    ): User {
        $reason = trim($reason);

        if ($reason === '') {
            throw new DomainException(
                'Ban reason cannot be empty.'
            );
        }

        if ($user->is_banned) {
            throw new DomainException(
                'This user is already banned.'
            );
        }

        return DB::transaction(
            function () use ($user, $reason): User {
                $user->forceFill([
                    'is_banned' => true,
                    'banned_at' => now(),
                    'ban_reason' => $reason,
                ])->save();

                $user->organizedEvents()
                    ->whereIn(
                        'status',
                        [
                            EventStatus::Draft->value,
                            EventStatus::Published->value,
                            EventStatus::Ongoing->value,
                        ]
                    )
                    ->update([
                        'status' => EventStatus::Cancelled->value,
                    ]);

                return $user->refresh();
            }
        );
    }
}
