<?php

namespace App\Filament\Resources\UmrahPackageResource\Pages;

use App\Filament\Resources\UmrahPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUmrahPackage extends CreateRecord
{
    protected static string $resource = UmrahPackageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}