<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Имя аккаунта')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        ignoreRecord: true
                    ),

                Toggle::make('is_admin')
                    ->label('Администратор')
                    ->helperText(
                        'Даёт пользователю доступ к административной панели.'
                    )
                    ->disabled(
                        fn ($record): bool => $record !== null
                            && $record->id === Auth::id()
                    ),

                TextInput::make('profile.first_name')
                    ->label('Имя')
                    ->maxLength(255),

                TextInput::make('profile.last_name')
                    ->label('Фамилия')
                    ->maxLength(255),

                DatePicker::make('profile.birth_date')
                    ->label('Дата рождения')
                    ->maxDate(now()),

                TextInput::make('profile.city')
                    ->label('Город')
                    ->maxLength(255),

                Textarea::make('profile.bio')
                    ->label('О себе')
                    ->rows(5)
                    ->maxLength(2000)
                    ->columnSpanFull(),
            ]);
    }
}
