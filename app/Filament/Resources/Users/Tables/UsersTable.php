<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use App\Actions\BanUserAction;
use App\Actions\UnbanUserAction;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Имя аккаунта')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('profile.first_name')
                    ->label('Имя')
                    ->searchable(),

                TextColumn::make('profile.last_name')
                    ->label('Фамилия')
                    ->searchable(),

                TextColumn::make('profile.city')
                    ->label('Город')
                    ->searchable()
                    ->placeholder('—'),

                IconColumn::make('email_verified_at')
                    ->label('Email подтверждён')
                    ->boolean(
                        fn ($state): bool =>
                            $state !== null
                    ),

                IconColumn::make('is_admin')
                    ->label('Админ')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_banned')
                    ->label('Заблокирован')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('banned_at')
                    ->label('Дата блокировки')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),

                TextColumn::make('ban_reason')
                    ->label('Причина блокировки')
                    ->placeholder('—')
                    ->limit(50)
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
        
                TextColumn::make('created_at')
                    ->label('Регистрация')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Изменён')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])
            ->filters([
                TernaryFilter::make('is_admin')
                    ->label('Администратор')
                    ->placeholder('Все пользователи')
                    ->trueLabel('Только администраторы')
                    ->falseLabel('Только обычные пользователи'),

                TernaryFilter::make('email_verified_at')
                    ->label('Подтверждение Email')
                    ->nullable()
                    ->placeholder('Все')
                    ->trueLabel('Подтверждён')
                    ->falseLabel('Не подтверждён'),

                TernaryFilter::make('is_admin')
                    ->label('Администратор')
                    ->placeholder('Все пользователи')
                    ->trueLabel('Только администраторы')
                    ->falseLabel('Только обычные пользователи'),
            ])


            ->recordActions([
                EditAction::make()
                    ->label('Редактировать'),

                Action::make('ban')
                    ->label('Заблокировать')
                    ->requiresConfirmation()
                    ->modalHeading('Заблокировать пользователя')
                    ->modalDescription(
                        'Пользователь потеряет доступ к аккаунту, а все его активные мероприятия будут отменены.'
                    )
                    ->form([
                        Textarea::make('reason')
                            ->label('Причина блокировки')
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->visible(
                        fn (User $record): bool =>
                            ! $record->is_banned
                            && $record->id !== Auth::id()
                    )
                    ->action(
                        function (
                            User $record,
                            array $data,
                        ): void {
                            app(BanUserAction::class)->execute(
                                user: $record,
                                reason: (string) $data['reason'],
                            );
                        }
                    ),

                Action::make('unban')
                    ->label('Разблокировать')
                    ->requiresConfirmation()
                    ->modalHeading('Разблокировать пользователя')
                    ->modalDescription(
                        'Доступ к аккаунту будет восстановлен. Отменённые мероприятия автоматически восстановлены не будут.'
                    )
                    ->visible(
                        fn (User $record): bool =>
                            $record->is_banned
                    )
                    ->action(
                        function (User $record): void {
                            app(UnbanUserAction::class)->execute(
                                $record
                            );
                        }
                    ),
            ])
            ->defaultSort(
                'created_at',
                'desc'
            );
    }
}