<?php

namespace App\Filament\Resources\DepartureScheduleResource\Pages;

use App\Filament\Resources\DepartureScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartureSchedule extends CreateRecord
{
    protected static string $resource = DepartureScheduleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}