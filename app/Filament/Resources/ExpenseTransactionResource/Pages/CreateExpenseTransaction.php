<?php

namespace App\Filament\Resources\ExpenseTransactionResource\Pages;

use App\Filament\Resources\ExpenseTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExpenseTransaction extends CreateRecord
{
    protected static string $resource = ExpenseTransactionResource::class;
}