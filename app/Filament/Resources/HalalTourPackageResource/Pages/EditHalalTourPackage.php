<?php

namespace App\Filament\Resources\HalalTourPackageResource\Pages;

use App\Filament\Resources\HalalTourPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHalalTourPackage extends EditRecord
{
    protected static string $resource = HalalTourPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}