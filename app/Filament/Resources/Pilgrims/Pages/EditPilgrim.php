<?php

namespace App\Filament\Resources\Pilgrims\Pages;

use App\Filament\Resources\Pilgrims\PilgrimResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPilgrim extends EditRecord
{
    protected static string $resource = PilgrimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
