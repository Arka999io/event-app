<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        function (
                            ?string $state,
                            callable $set,
                        ): void {
                            $set(
                                'slug',
                                Str::slug($state ?? '')
                            );
                        }
                    ),

                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        ignoreRecord: true
                    )
                    ->helperText(
                        'Используется как технический идентификатор категории.'
                    ),

                Toggle::make('is_active')
                    ->label('Активна')
                    ->helperText(
                        'Неактивные категории нельзя выбрать при создании нового мероприятия.'
                    )
                    ->default(true),
            ]);
    }
}