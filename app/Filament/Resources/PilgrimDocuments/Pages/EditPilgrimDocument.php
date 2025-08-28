<?php

namespace App\Filament\Resources\PilgrimDocuments\Pages;

use App\Filament\Resources\PilgrimDocuments\PilgrimDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPilgrimDocument extends EditRecord
{
    protected static string $resource = PilgrimDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
