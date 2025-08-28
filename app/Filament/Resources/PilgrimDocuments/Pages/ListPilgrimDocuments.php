<?php

namespace App\Filament\Resources\PilgrimDocuments\Pages;

use App\Filament\Resources\PilgrimDocuments\PilgrimDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPilgrimDocuments extends ListRecords
{
    protected static string $resource = PilgrimDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
