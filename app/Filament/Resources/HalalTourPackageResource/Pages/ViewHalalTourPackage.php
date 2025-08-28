<?php

namespace App\Filament\Resources\HalalTourPackageResource\Pages;

use App\Filament\Resources\HalalTourPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHalalTourPackage extends ViewRecord
{
    protected static string $resource = HalalTourPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}