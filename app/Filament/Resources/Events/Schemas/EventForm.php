<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Enums\EventStatus;
use App\Enums\GenderPreference;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('organizer_id')
                    ->label('Организатор')
                    ->relationship('organizer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('title')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Описание')
                    ->required()
                    ->rows(6)
                    ->columnSpanFull(),

                DateTimePicker::make('starts_at')
                    ->label('Начало')
                    ->required(),

                DateTimePicker::make('ends_at')
                    ->label('Окончание')
                    ->after('starts_at'),

                TextInput::make('meeting_address')
                    ->label('Место встречи')
                    ->maxLength(255),

                TextInput::make('venue_address')
                    ->label('Адрес проведения')
                    ->required()
                    ->maxLength(255),

                TextInput::make('min_age')
                    ->label('Минимальный возраст')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(120),

                TextInput::make('max_age')
                    ->label('Максимальный возраст')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(120),

                Select::make('gender_preference')
                    ->label('Предпочтение по полу')
                    ->options([
                        GenderPreference::Any->value => 'Неважно',
                        GenderPreference::Male->value => 'Мужчины',
                        GenderPreference::Female->value => 'Женщины',
                    ])
                    ->required(),

                TextInput::make('spots_total')
                    ->label('Всего мест')
                    ->required()
                    ->numeric()
                    ->minValue(1),

                TextInput::make('spots_taken')
                    ->label('Занято мест')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),

                Select::make('status')
                    ->label('Статус')
                    ->options([
                        EventStatus::Draft->value => 'Черновик',
                        EventStatus::Published->value => 'Опубликовано',
                        EventStatus::Ongoing->value => 'Идёт сейчас',
                        EventStatus::Completed->value => 'Завершено',
                        EventStatus::Cancelled->value => 'Отменено',
                    ])
                    ->required(),

                DateTimePicker::make('published_at')
                    ->label('Дата публикации')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }
}
