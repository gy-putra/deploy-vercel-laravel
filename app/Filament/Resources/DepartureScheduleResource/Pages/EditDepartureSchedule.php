<?php

namespace App\Filament\Resources\DepartureScheduleResource\Pages;

use App\Filament\Resources\DepartureScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepartureSchedule extends EditRecord
{
    protected static string $resource = DepartureScheduleResource::class;

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