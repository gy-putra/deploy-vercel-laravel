<?php

namespace App\Filament\Resources\Pilgrims\Pages;

use App\Filament\Resources\Pilgrims\PilgrimResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPilgrim extends ViewRecord
{
    protected static string $resource = PilgrimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
