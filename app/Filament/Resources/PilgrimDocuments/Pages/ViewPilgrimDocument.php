<?php

namespace App\Filament\Resources\PilgrimDocuments\Pages;

use App\Filament\Resources\PilgrimDocuments\PilgrimDocumentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPilgrimDocument extends ViewRecord
{
    protected static string $resource = PilgrimDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
