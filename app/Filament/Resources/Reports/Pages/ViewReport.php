<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Actions\ResolveEventReportByCancellationAction;
use App\Actions\ResolveMessageReportByBanAction;
use App\Actions\ResolveUserReportByBanAction;
use App\Enums\EventStatus;
use App\Enums\ReportStatus;
use App\Filament\Resources\Reports\ReportResource;
use App\Models\Event;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            /*
             * Жалоба на пользователя:
             * открыть его публичный профиль.
             */
            Action::make('openUser')
                ->label('Открыть профиль')
                ->visible(function (): bool {
                    /** @var Report $report */
                    $report = $this->record;

                    return $report->reportable instanceof User;
                })
                ->url(function (): string {
                    /** @var Report $report */
                    $report = $this->record;

                    /** @var User $user */
                    $user = $report->reportable;

                    return route(
                        'users.show',
                        $user
                    );
                })
                ->openUrlInNewTab(),

            /*
             * Жалоба на мероприятие:
             * открыть страницу мероприятия.
             */
            Action::make('openEvent')
                ->label('Открыть мероприятие')
                ->visible(function (): bool {
                    /** @var Report $report */
                    $report = $this->record;

                    return $report->reportable instanceof Event;
                })
                ->url(function (): string {
                    /** @var Report $report */
                    $report = $this->record;

                    /** @var Event $event */
                    $event = $report->reportable;

                    return route(
                        'events.show',
                        $event
                    );
                })
                ->openUrlInNewTab(),

            /*
             * Жалоба непосредственно на пользователя:
             * заблокировать пользователя.
             */
            Action::make('banUser')
                ->label('Заблокировать пользователя')
                ->requiresConfirmation()
                ->modalHeading(
                    'Заблокировать пользователя'
                )
                ->modalDescription(
                    'Пользователь потеряет доступ к аккаунту, '
                    .'а все его активные мероприятия будут отменены. '
                    .'Жалоба будет отмечена как решённая.'
                )
                ->form([
                    Textarea::make('reason')
                        ->label('Причина блокировки')
                        ->required()
                        ->maxLength(2000),
                ])
                ->visible(function (): bool {
                    /** @var Report $report */
                    $report = $this->record;

                    if (! $report->reportable instanceof User) {
                        return false;
                    }

                    if ($report->reportable->is_banned) {
                        return false;
                    }

                    return ! in_array(
                        $report->status,
                        [
                            ReportStatus::Resolved,
                            ReportStatus::Dismissed,
                        ],
                        true
                    );
                })
                ->action(function (array $data): void {
                    /** @var Report $report */
                    $report = $this->record;

                    app(
                        ResolveUserReportByBanAction::class
                    )->execute(
                        report: $report,
                        reason: (string) $data['reason'],
                    );
                }),

            /*
             * Жалоба на сообщение:
             * заблокировать автора этого сообщения.
             */
            Action::make('banMessageAuthor')
                ->label('Заблокировать автора')
                ->requiresConfirmation()
                ->modalHeading(
                    'Заблокировать автора сообщения'
                )
                ->modalDescription(
                    'Автор сообщения потеряет доступ к аккаунту, '
                    .'а все его активные мероприятия будут отменены. '
                    .'Жалоба будет отмечена как решённая.'
                )
                ->form([
                    Textarea::make('reason')
                        ->label('Причина блокировки')
                        ->required()
                        ->maxLength(2000),
                ])
                ->visible(function (): bool {
                    /** @var Report $report */
                    $report = $this->record;

                    if (! $report->reportable instanceof Message) {
                        return false;
                    }

                    if ($report->reportable->sender->is_banned) {
                        return false;
                    }

                    return ! in_array(
                        $report->status,
                        [
                            ReportStatus::Resolved,
                            ReportStatus::Dismissed,
                        ],
                        true
                    );
                })
                ->action(function (array $data): void {
                    /** @var Report $report */
                    $report = $this->record;

                    app(
                        ResolveMessageReportByBanAction::class
                    )->execute(
                        report: $report,
                        reason: (string) $data['reason'],
                    );
                }),

            /*
             * Жалоба на мероприятие:
             * отменить мероприятие.
             */
            Action::make('cancelEvent')
                ->label('Отменить мероприятие')
                ->requiresConfirmation()
                ->modalHeading(
                    'Отменить мероприятие'
                )
                ->modalDescription(
                    'Мероприятие будет отменено. '
                    .'Новые отклики и сообщения станут недоступны. '
                    .'Жалоба будет отмечена как решённая.'
                )
                ->visible(function (): bool {
                    /** @var Report $report */
                    $report = $this->record;

                    if (! $report->reportable instanceof Event) {
                        return false;
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
                        return false;
                    }

                    return ! in_array(
                        $report->reportable->status,
                        [
                            EventStatus::Completed,
                            EventStatus::Cancelled,
                        ],
                        true
                    );
                })
                ->action(function (): void {
                    /** @var Report $report */
                    $report = $this->record;

                    app(
                        ResolveEventReportByCancellationAction::class
                    )->execute($report);
                }),

            /*
             * Ручное изменение статуса жалобы.
             */
            EditAction::make()
                ->label('Изменить статус'),
        ];
    }
}
