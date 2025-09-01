<?php

namespace App\Filament\Resources\ExpenseTransactionResource\Pages;

use App\Filament\Resources\ExpenseTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExpenseTransaction extends ViewRecord
{
    protected static string $resource = ExpenseTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}