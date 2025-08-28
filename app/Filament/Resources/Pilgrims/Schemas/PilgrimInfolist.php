<?php

namespace App\Filament\Resources\Pilgrims\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PilgrimInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('nik'),
                TextEntry::make('passport_number'),
                TextEntry::make('phone'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('package_name')
                    ->label('Package'),
                TextEntry::make('registration_date')
                    ->date(),
                TextEntry::make('payment_status'),
                TextEntry::make('status'),
                TextEntry::make('birth_date')
                    ->date(),
                TextEntry::make('gender'),
                TextEntry::make('emergency_contact_name'),
                TextEntry::make('emergency_contact_phone'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
