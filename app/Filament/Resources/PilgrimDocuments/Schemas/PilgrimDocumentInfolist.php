<?php

namespace App\Filament\Resources\PilgrimDocuments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Illuminate\Support\Facades\Storage;
use Filament\Schemas\Schema;

class PilgrimDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('pilgrim.name')
                    ->label('Pilgrim Name')
                    ->formatStateUsing(function ($record) {
                        if (!$record->pilgrim) {
                            return 'No pilgrim assigned';
                        }
                        
                        return $record->pilgrim->name;
                    }),
                    
                TextEntry::make('document_type')
                    ->label('Document Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ktp' => 'KTP (ID Card)',
                        'kk' => 'KK (Family Card)',
                        'passport' => 'Passport',
                        'visa' => 'Visa',
                        'marriage_certificate' => 'Marriage Certificate',
                        'birth_certificate' => 'Birth Certificate',
                        'transfer_proof' => 'Transfer Proof',
                        'vaccine' => 'Vaccine Certificate',
                        'ticket' => 'Flight Ticket',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ktp', 'kk' => 'info',
                        'passport' => 'primary',
                        'visa' => 'success',
                        'marriage_certificate', 'birth_certificate' => 'warning',
                        'transfer_proof' => 'secondary',
                        'vaccine' => 'orange',
                        'ticket' => 'purple',
                        default => 'gray',
                    }),
                    
                TextEntry::make('files')
                    ->label('Document Files')
                    ->formatStateUsing(function ($state): string {
                        if (!$state || !is_array($state) || empty($state)) {
                            return 'No files uploaded';
                        }
                        
                        $fileLinks = [];
                        foreach ($state as $file) {
                            $url = Storage::disk('public')->url($file);
                            $filename = basename($file);
                            $fileLinks[] = "<a href='{$url}' target='_blank' class='text-primary-600 hover:text-primary-500 underline'>{$filename}</a>";
                        }
                        
                        return implode('<br>', $fileLinks);
                    })
                    ->html(),
                    
                TextEntry::make('formatted_file_size')
                    ->label('File Size'),
                    
                IconEntry::make('is_optional')
                    ->label('Optional Document')
                    ->boolean(),
                    
                TextEntry::make('created_at')
                    ->label('Uploaded At')
                    ->dateTime(),
                    
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
