<?php

namespace App\Actions;

use App\Enums\EventStatus;
use App\Enums\ReportStatus;
use App\Models\Event;
use App\Models\Report;
use DomainException;
use Illuminate\Support\Facades\DB;

class ResolveEventReportByCancellationAction
{
    public function __construct(
        private readonly CancelEventAction $cancelEvent,
    ) {}

    public function execute(Report $report): Report
    {
        if (! $report->reportable instanceof Event) {
            throw new DomainException(
                'This report is not about an event.'
            );
        }

        if (
            in_array(
                $report->status,
                [
                    ReportStatus::Resolved,
                    ReportStatus::Dismissed,
                ],
                true
            )
        ) {
            throw new DomainException(
                'This report is already closed.'
            );
        }

        /** @var Event $event */
        $event = $report->reportable;

        if ($event->status === EventStatus::Cancelled) {
            throw new DomainException(
                'This event is already cancelled.'
            );
        }

        if ($event->status === EventStatus::Completed) {
            throw new DomainException(
                'A completed event cannot be cancelled.'
            );
        }

        return DB::transaction(
            function () use ($report, $event): Report {
                $this->cancelEvent->execute($event);

                $report->update([
                    'status' => ReportStatus::Resolved,
                ]);

                return $report->refresh();
            }
        );
    }
}