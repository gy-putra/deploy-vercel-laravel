<?php

namespace App\Filament\Resources\PilgrimDocuments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Facades\Storage;
use Filament\Schemas\Schema;

class PilgrimDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('pilgrim.name'),
                TextEntry::make('document_type'),
                TextEntry::make('file')
                    ->label('Document File')
                    ->formatStateUsing(function (?string $state): ?string {
                        if (!$state) {
                            return 'No file uploaded';
                        }
                        
                        $url = Storage::disk('public')->url($state);
                        $filename = basename($state);
                        
                        return "<a href='{$url}' target='_blank' class='text-primary-600 hover:text-primary-500 underline'>{$filename}</a>";
                    })
                    ->html(),
                TextEntry::make('description')
                    ->label('Description')
                    ->placeholder('No description provided'),
                TextEntry::make('category')
                    ->label('Category')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'passport' => 'primary',
                        'visa' => 'success',
                        'vaccine' => 'warning',
                        'ticket' => 'info',
                        'other' => 'gray',
                        default => 'gray',
                    }),
                TextEntry::make('formatted_file_size')
                    ->label('File Size'),
                TextEntry::make('status'),
                TextEntry::make('uploaded_at')
                    ->dateTime(),
                TextEntry::make('verified_at')
                    ->dateTime(),
                TextEntry::make('verified_by')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
