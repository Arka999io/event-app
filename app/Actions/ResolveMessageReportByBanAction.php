<?php

namespace App\Actions;

use App\Enums\ReportStatus;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class ResolveMessageReportByBanAction
{
    public function __construct(
        private readonly BanUserAction $banUser,
    ) {}

    public function execute(
        Report $report,
        string $reason,
    ): Report {
        if (! $report->reportable instanceof Message) {
            throw new DomainException(
                'This report is not about a message.'
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

        /** @var Message $message */
        $message = $report->reportable;

        /** @var User $sender */
        $sender = $message->sender;

        return DB::transaction(
            function () use (
                $report,
                $sender,
                $reason,
            ): Report {
                $this->banUser->execute(
                    user: $sender,
                    reason: $reason,
                );

                $report->update([
                    'status' => ReportStatus::Resolved,
                ]);

                return $report->refresh();
            }
        );
    }
}