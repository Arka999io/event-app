<?php

namespace App\Filament\Resources\Reports\Tables;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\Event;
use App\Models\Message;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reporter.name')
                    ->label('Автор')
                    ->searchable(),

                TextColumn::make('reportable_type')
                    ->label('Объект')
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            User::class => 'Пользователь',
                            Event::class => 'Мероприятие',
                            Message::class => 'Сообщение',
                            default => $state ?? 'Неизвестно',
                        }
                    ),

                TextColumn::make('reportable_id')
                    ->label('ID')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Причина')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string => match (
                            $state instanceof ReportReason
                                ? $state
                                : ReportReason::from((string) $state)
                        ) {
                            ReportReason::Spam => 'Спам',
                            ReportReason::Harassment => 'Оскорбления',
                            ReportReason::InappropriateContent => 'Неприемлемый контент',
                            ReportReason::Scam => 'Мошенничество',
                            ReportReason::Other => 'Другое',
                        }
                    ),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string => match (
                            $state instanceof ReportStatus
                                ? $state
                                : ReportStatus::from((string) $state)
                        ) {
                            ReportStatus::Pending => 'Ожидает рассмотрения',

                            ReportStatus::Reviewing => 'На рассмотрении',

                            ReportStatus::Resolved => 'Решена',

                            ReportStatus::Dismissed => 'Отклонена',
                        }
                    ),

                TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Изменена')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        ReportStatus::Pending->value => 'Ожидает рассмотрения',

                        ReportStatus::Reviewing->value => 'На рассмотрении',

                        ReportStatus::Resolved->value => 'Решена',

                        ReportStatus::Dismissed->value => 'Отклонена',
                    ]),

                SelectFilter::make('reason')
                    ->label('Причина')
                    ->options([
                        ReportReason::Spam->value => 'Спам',
                        ReportReason::Harassment->value => 'Оскорбления',
                        ReportReason::InappropriateContent->value => 'Неприемлемый контент',
                        ReportReason::Scam->value => 'Мошенничество',
                        ReportReason::Other->value => 'Другое',
                    ]),

                SelectFilter::make('reportable_type')
                    ->label('Тип объекта')
                    ->options([
                        User::class => 'Пользователь',
                        Event::class => 'Мероприятие',
                        Message::class => 'Сообщение',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Просмотреть'),

                EditAction::make()
                    ->label('Изменить статус'),
            ])
            ->defaultSort(
                'created_at',
                'desc'
            );
    }
}
