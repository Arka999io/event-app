<?php

namespace App\Filament\Resources\Reports\Schemas;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\Event;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Schema;

class ReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('reporter.name')
                    ->label('Кто отправил жалобу'),

                TextEntry::make('reporter.email')
                    ->label('Email отправителя'),

                TextEntry::make('reportable_type')
                    ->label('Тип объекта жалобы')
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            User::class => 'Пользователь',
                            Event::class => 'Мероприятие',
                            Message::class => 'Сообщение',
                            default => $state ?? 'Неизвестно',
                        }
                    ),

                TextEntry::make('reportable')
                    ->label('На кого / что отправлена жалоба')
                    ->formatStateUsing(
                        function ($state): string {
                            if ($state instanceof User) {
                                /** @var \App\Models\Profile|null $profile */
                                $profile = $state->profile;

                                $firstName = $profile !== null
                                    ? $profile->first_name
                                    : '';

                                $lastName = $profile !== null
                                    ? ($profile->last_name ?? '')
                                    : '';

                                $fullName = trim(
                                    $firstName.' '.$lastName
                                );

                                if ($fullName === '') {
                                    $fullName = $state->name;
                                }

                                return $fullName
                                    .' — '
                                    .$state->email
                                    .' (ID: '
                                    .$state->id
                                    .')';
                            }

                            if ($state instanceof Event) {
                                return $state->title
                                    .' (ID: '
                                    .$state->id
                                    .')';
                            }

                            if ($state instanceof Message) {
                                return 'Сообщение #'
                                    .$state->id;
                            }

                            return 'Объект недоступен';
                        }
                    ),

                /*
                 * Дополнительная информация для жалобы
                 * на сообщение.
                 */
                TextEntry::make('reportable.sender.name')
                    ->label('Автор сообщения')
                    ->visible(
                        fn (Report $record): bool =>
                            $record->reportable instanceof Message
                    ),

                TextEntry::make('reportable.sender.email')
                    ->label('Email автора сообщения')
                    ->visible(
                        fn (Report $record): bool =>
                            $record->reportable instanceof Message
                    ),

                TextEntry::make('reportable.body')
                    ->label('Текст сообщения')
                    ->placeholder('Сообщение недоступно')
                    ->columnSpanFull()
                    ->visible(
                        fn (Report $record): bool =>
                            $record->reportable instanceof Message
                    ),

                TextEntry::make(
                    'reportable.conversation.event.title'
                )
                    ->label('Мероприятие')
                    ->placeholder('—')
                    ->visible(
                        fn (Report $record): bool =>
                            $record->reportable instanceof Message
                    ),

                TextEntry::make(
                    'reportable.conversation_id'
                )
                    ->label('ID чата')
                    ->visible(
                        fn (Report $record): bool =>
                            $record->reportable instanceof Message
                    ),

                /*
                 * Контекст переписки:
                 * до 10 сообщений перед объектом жалобы,
                 * само сообщение и до 10 сообщений после.
                 */
                ViewEntry::make('message_context')
                    ->label('Контекст переписки')
                    ->view(
                        'filament.reports.message-context'
                    )
                    ->visible(
                        fn (Report $record): bool =>
                            $record->reportable instanceof Message
                    )
                    ->columnSpanFull(),

                TextEntry::make('reason')
                    ->label('Причина')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string => match (
                            $state instanceof ReportReason
                                ? $state
                                : ReportReason::from(
                                    (string) $state
                                )
                        ) {
                            ReportReason::Spam =>
                                'Спам',

                            ReportReason::Harassment =>
                                'Оскорбления или преследование',

                            ReportReason::InappropriateContent =>
                                'Неприемлемый контент',

                            ReportReason::Scam =>
                                'Мошенничество',

                            ReportReason::Other =>
                                'Другое',
                        }
                    ),

                TextEntry::make('description')
                    ->label('Описание жалобы')
                    ->placeholder('Описание не указано')
                    ->columnSpanFull(),

                TextEntry::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string => match (
                            $state instanceof ReportStatus
                                ? $state
                                : ReportStatus::from(
                                    (string) $state
                                )
                        ) {
                            ReportStatus::Pending =>
                                'Ожидает рассмотрения',

                            ReportStatus::Reviewing =>
                                'На рассмотрении',

                            ReportStatus::Resolved =>
                                'Решена',

                            ReportStatus::Dismissed =>
                                'Отклонена',
                        }
                    ),

                TextEntry::make('created_at')
                    ->label('Жалоба создана')
                    ->dateTime('d.m.Y H:i'),

                TextEntry::make('updated_at')
                    ->label('Последнее изменение')
                    ->dateTime('d.m.Y H:i'),
            ]);
    }
}