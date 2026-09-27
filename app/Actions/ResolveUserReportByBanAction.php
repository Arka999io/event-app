<?php

namespace App\Actions;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class ResolveUserReportByBanAction
{
    public function __construct(
        private readonly BanUserAction $banUser,
    ) {}

    public function execute(
        Report $report,
        string $reason,
    ): Report {
        if (! $report->reportable instanceof User) {
            throw new DomainException(
                'This report is not about a user.'
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

        return DB::transaction(
            function () use ($report, $reason): Report {
                /** @var User $user */
                $user = $report->reportable;

                $this->banUser->execute(
                    user: $user,
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