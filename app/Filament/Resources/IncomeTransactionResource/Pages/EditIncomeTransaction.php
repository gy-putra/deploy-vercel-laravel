<?php

namespace App\Filament\Resources\IncomeTransactionResource\Pages;

use App\Filament\Resources\IncomeTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIncomeTransaction extends EditRecord
{
    protected static string $resource = IncomeTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}