<?php

namespace App\Filament\Resources\Pilgrims\Schemas;

use App\Models\UmrahPackage;
use App\Models\HalalTourPackage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PilgrimForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('nik'),
                TextInput::make('passport_number'),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                Textarea::make('address')
                    ->columnSpanFull(),
                Select::make('package_name')
                    ->label('Package')
                    ->options(function () {
                        $umrahPackages = UmrahPackage::where('is_active', true)
                            ->pluck('name', 'name')
                            ->toArray();
                        
                        $halalTourPackages = HalalTourPackage::where('is_active', true)
                            ->pluck('name', 'name')
                            ->toArray();
                        
                        return array_merge($umrahPackages, $halalTourPackages);
                    })
                    ->searchable()
                    ->required(),
                DatePicker::make('registration_date')
                    ->required(),
                Select::make('payment_status')
                    ->options(['pending' => 'Pending', 'partial' => 'Partial', 'paid' => 'Paid', 'refunded' => 'Refunded'])
                    ->default('pending')
                    ->required(),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'processing' => 'Processing',
            'ready_to_depart' => 'Ready to depart',
            'completed' => 'Completed',
        ])
                    ->default('pending')
                    ->required(),
                DatePicker::make('birth_date'),
                Select::make('gender')
                    ->options(['male' => 'Male', 'female' => 'Female'])
                    ->required(),
                TextInput::make('emergency_contact_name'),
                TextInput::make('emergency_contact_phone')
                    ->tel(),
            ]);
    }
}
