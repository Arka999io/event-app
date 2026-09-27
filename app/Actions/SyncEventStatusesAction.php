<?php

namespace App\Actions;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Support\Carbon;

class SyncEventStatusesAction
{
    /**
     * @return array{started: int, completed: int}
     */
    public function execute(): array
    {
        $now = Carbon::now();

        $started = Event::query()
            ->where('status', EventStatus::Published->value)
            ->where('starts_at', '<=', $now)
            ->update([
                'status' => EventStatus::Ongoing->value,
            ]);

        $completed = Event::query()
            ->where('status', EventStatus::Ongoing->value)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', $now)
            ->update([
                'status' => EventStatus::Completed->value,
            ]);

        return [
            'started' => $started,
            'completed' => $completed,
        ];
    }
}