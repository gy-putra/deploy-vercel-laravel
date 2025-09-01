<?php

namespace App\Filament\Resources\ExpenseTransactionResource\Pages;

use App\Filament\Resources\ExpenseTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListExpenseTransactions extends ListRecords
{
    protected static string $resource = ExpenseTransactionResource::class;

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