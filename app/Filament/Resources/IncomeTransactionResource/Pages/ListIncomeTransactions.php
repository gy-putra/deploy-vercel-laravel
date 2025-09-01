<?php

namespace App\Filament\Resources\IncomeTransactionResource\Pages;

use App\Filament\Resources\IncomeTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListIncomeTransactions extends ListRecords
{
    protected static string $resource = IncomeTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * Modify the table query to ensure no duplicate records.
     */
    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery();
    }
}