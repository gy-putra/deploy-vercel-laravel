<?php

namespace App\Filament\Resources\PilgrimDocuments\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PilgrimDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pilgrim_id')
                    ->label('Pilgrim')
                    ->relationship(
                        name: 'pilgrim',
                        titleAttribute: 'name'
                    )
                    ->required()
                    ->searchable(['name', 'nik', 'package_name'])
                    ->preload()
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return $record->name;
                    })
                    ->getSearchResultsUsing(function (string $search) {
                        return \App\Models\Pilgrim::where(function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                  ->orWhere('nik', 'like', "%{$search}%")
                                  ->orWhere('package_name', 'like', "%{$search}%");
                        })
                        ->limit(50)
                        ->get()
                        ->mapWithKeys(function ($pilgrim) {
                            return [$pilgrim->id => $pilgrim->name];
                        });
                    })
                    ->placeholder('Search by name, NIK, or package...')
                    ->helperText('Select the pilgrim for whom this document belongs. You can search by name, NIK, or package name.')
                    ->createOptionForm([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter pilgrim full name'),
                        \Filament\Forms\Components\TextInput::make('nik')
                            ->label('NIK (National ID Number)')
                            ->maxLength(16)
                            ->placeholder('Enter 16-digit NIK (optional)'),
                        \Filament\Forms\Components\TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(15)
                            ->placeholder('Enter phone number (optional)'),
                        \Filament\Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('Enter email address (optional)'),
                        \Filament\Forms\Components\TextInput::make('package_name')
                            ->label('Package Name')
                            ->maxLength(255)
                            ->placeholder('Enter package name (optional)'),
                    ])
                    ->createOptionUsing(function (array $data) {
                        return \App\Models\Pilgrim::create($data);
                    })
                    ->createOptionModalHeading('Create New Pilgrim')
                    ->editOptionForm([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('nik')
                            ->label('NIK (National ID Number)')
                            ->maxLength(16),
                        \Filament\Forms\Components\TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(15),
                        \Filament\Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('package_name')
                            ->label('Package Name')
                            ->maxLength(255),
                    ])
                    ->editOptionModalHeading('Edit Pilgrim Information'),
                
                Select::make('document_type')
                    ->options([
                        'ktp' => 'KTP (ID Card)',
                        'kk' => 'KK (Family Card)',
                        'passport' => 'Passport',
                        'visa' => 'Visa',
                        'marriage_certificate' => 'Marriage Certificate',
                        'birth_certificate' => 'Birth Certificate',
                        'transfer_proof' => 'Transfer Proof',
                        'vaccine' => 'Vaccine Certificate',
                        'ticket' => 'Flight Ticket'
                    ])
                    ->required()
                    ->searchable(),
                
                FileUpload::make('files')
                    ->label('Document Files')
                    ->directory('pilgrim-documents')
                    ->disk('public')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'])
                    ->maxSize(10240) // 10MB
                    ->multiple()
                    ->maxFiles(5)
                    ->required()
                    ->downloadable()
                    ->previewable()
                    ->reorderable()
                    ->columnSpanFull(),
                
                Toggle::make('is_optional')
                    ->label('Is Optional Document')
                    ->default(false)
                    ->helperText('Mark if this document is optional for the pilgrim'),
            ]);
    }
}
