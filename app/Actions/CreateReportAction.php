<?php

namespace App\Actions;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\Event;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Model;

class CreateReportAction
{
    public function execute(
        User $reporter,
        Model $reportable,
        ReportReason $reason,
        ?string $description = null,
    ): Report {
        if (
            ! $reportable instanceof User
            && ! $reportable instanceof Event
            && ! $reportable instanceof Message
        ) {
            throw new DomainException(
                'This type of content cannot be reported.'
            );
        }

        if (
            $reportable instanceof User
            && $reportable->id === $reporter->id
        ) {
            throw new DomainException(
                'You cannot report yourself.'
            );
        }

        if ($reportable instanceof Message) {
            $conversation = $reportable->conversation;
            $event = $conversation->event;

            if (
                $reporter->id !== $conversation->user_id
                && $reporter->id !== $event->organizer_id
            ) {
                throw new DomainException(
                    'You cannot report a message from this conversation.'
                );
            }
        }

        $alreadyExists = Report::query()
            ->where('reporter_id', $reporter->id)
            ->where('reportable_type', $reportable->getMorphClass())
            ->where('reportable_id', $reportable->getKey())
            ->whereIn('status', [
                ReportStatus::Pending->value,
                ReportStatus::Reviewing->value,
            ])
            ->exists();

        if ($alreadyExists) {
            throw new DomainException(
                'You already have an active report for this content.'
            );
        }

        return Report::query()->create([
            'reporter_id' => $reporter->id,
            'reportable_type' => $reportable->getMorphClass(),
            'reportable_id' => $reportable->getKey(),
            'reason' => $reason,
            'description' => $description,
            'status' => ReportStatus::Pending,
        ]);
    }
}
