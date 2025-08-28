<?php

namespace App\Filament\Resources\PilgrimDocuments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class PilgrimDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pilgrim_id')
                    ->relationship('pilgrim', 'name')
                    ->required(),
                Select::make('document_type')
                    ->options(['passport' => 'Passport', 'visa' => 'Visa', 'vaccine' => 'Vaccine', 'ticket' => 'Ticket'])
                    ->required(),
                FileUpload::make('file')
                    ->label('Document File')
                    ->directory('pilgrim-documents')
                    ->disk('public')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'])
                    ->maxSize(10240) // 10MB
                    ->required()
                    ->downloadable()
                    ->previewable()
                    ->afterStateUpdated(function (callable $set, $state) {
                        if ($state) {
                            $set('uploaded_at', now());
                        }
                    }),
                TextInput::make('description')
                    ->label('Description')
                    ->placeholder('Optional description of the document')
                    ->columnSpanFull(),
                Select::make('category')
                    ->label('Category')
                    ->options([
                        'passport' => 'Passport',
                        'visa' => 'Visa',
                        'vaccine' => 'Vaccine Certificate',
                        'ticket' => 'Flight Ticket',
                        'other' => 'Other'
                    ])
                    ->placeholder('Select document category (optional)'),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                    ->default('pending')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                DateTimePicker::make('uploaded_at')
                    ->disabled()
                    ->dehydrated()
                    ->hidden(),
                DateTimePicker::make('verified_at'),
                TextInput::make('verified_by')
                    ->numeric(),
            ]);
    }
}
