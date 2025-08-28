<?php

namespace App\Filament\Resources\HalalTourPackageResource\Pages;

use App\Filament\Resources\HalalTourPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHalalTourPackages extends ListRecords
{
    protected static string $resource = HalalTourPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}