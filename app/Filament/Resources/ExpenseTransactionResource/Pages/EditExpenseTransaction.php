<?php

namespace App\Filament\Resources\ExpenseTransactionResource\Pages;

use App\Filament\Resources\ExpenseTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpenseTransaction extends EditRecord
{
    protected static string $resource = ExpenseTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}