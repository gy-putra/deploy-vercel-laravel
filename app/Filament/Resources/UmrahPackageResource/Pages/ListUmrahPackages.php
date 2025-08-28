<?php

namespace App\Filament\Resources\UmrahPackageResource\Pages;

use App\Filament\Resources\UmrahPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUmrahPackages extends ListRecords
{
    protected static string $resource = UmrahPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}