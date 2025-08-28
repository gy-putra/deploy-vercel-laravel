<?php

namespace App\Filament\Resources\QuotaResource\Pages;

use App\Filament\Resources\QuotaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateQuota extends CreateRecord
{
    protected static string $resource = QuotaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}