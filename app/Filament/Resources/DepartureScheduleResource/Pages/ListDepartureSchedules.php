<?php

namespace App\Filament\Resources\DepartureScheduleResource\Pages;

use App\Filament\Resources\DepartureScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepartureSchedules extends ListRecords
{
    protected static string $resource = DepartureScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}