<?php

namespace App\Filament\Resources\HalalTourPackageResource\Pages;

use App\Filament\Resources\HalalTourPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHalalTourPackage extends CreateRecord
{
    protected static string $resource = HalalTourPackageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}