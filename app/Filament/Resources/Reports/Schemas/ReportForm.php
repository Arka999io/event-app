<?php

namespace App\Filament\Resources\Reports\Schemas;

use App\Enums\ReportStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('description')
                    ->label('Описание жалобы')
                    ->disabled()
                    ->dehydrated(false)
                    ->rows(6)
                    ->columnSpanFull(),

                Select::make('status')
                    ->label('Статус')
                    ->options([
                        ReportStatus::Pending->value =>
                            'Ожидает рассмотрения',

                        ReportStatus::Reviewing->value =>
                            'На рассмотрении',

                        ReportStatus::Resolved->value =>
                            'Решена',

                        ReportStatus::Dismissed->value =>
                            'Отклонена',
                    ])
                    ->required(),
            ]);
    }
}