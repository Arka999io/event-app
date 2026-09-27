<?php

namespace App\Filament\Resources\Events\Tables;

use App\Enums\EventStatus;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('organizer.name')
                    ->label('Организатор')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Категория')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string => match (
                            $state instanceof EventStatus
                                ? $state
                                : EventStatus::from((string) $state)
                        ) {
                            EventStatus::Draft => 'Черновик',
                            EventStatus::Published => 'Опубликовано',
                            EventStatus::Ongoing => 'Идёт сейчас',
                            EventStatus::Completed => 'Завершено',
                            EventStatus::Cancelled => 'Отменено',
                        }
                    ),

                TextColumn::make('starts_at')
                    ->label('Начало')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('spots_taken')
                    ->label('Занято')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('spots_total')
                    ->label('Всего мест')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('venue_address')
                    ->label('Адрес')
                    ->searchable()
                    ->limit(35)
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),

                TextColumn::make('created_at')
                    ->label('Создано')
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
                        EventStatus::Draft->value => 'Черновики',
                        EventStatus::Published->value => 'Опубликованные',
                        EventStatus::Ongoing->value => 'Идут сейчас',
                        EventStatus::Completed->value => 'Завершённые',
                        EventStatus::Cancelled->value => 'Отменённые',
                    ]),

                SelectFilter::make('category_id')
                    ->label('Категория')
                    ->relationship(
                        'category',
                        'name'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('organizer_id')
                    ->label('Организатор')
                    ->relationship(
                        'organizer',
                        'name'
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Редактировать'),
            ])
            ->defaultSort(
                'starts_at',
                'desc'
            );
    }
}