<?php

namespace App\Console\Commands;

use App\Actions\SyncEventStatusesAction;
use Illuminate\Console\Command;

class SyncEventStatuses extends Command
{
    /**
     * @var string
     */
    protected $signature = 'events:sync-statuses';

    /**
     * @var string
     */
    protected $description = 'Synchronize event statuses based on their dates';

    public function handle(
        SyncEventStatusesAction $action,
    ): int {
        $result = $action->execute();

        $this->info(
            sprintf(
                'Started: %d, completed: %d.',
                $result['started'],
                $result['completed'],
            )
        );

        return self::SUCCESS;
    }
}