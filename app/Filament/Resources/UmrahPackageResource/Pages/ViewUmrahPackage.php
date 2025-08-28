<?php

namespace App\Filament\Resources\UmrahPackageResource\Pages;

use App\Filament\Resources\UmrahPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUmrahPackage extends ViewRecord
{
    protected static string $resource = UmrahPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}