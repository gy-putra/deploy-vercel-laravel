<?php

namespace App\Filament\Resources\PilgrimDocuments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class PilgrimDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pilgrim.name')
                    ->searchable(),
                TextColumn::make('document_type'),
                TextColumn::make('file')
                    ->label('Document File')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state) {
                            return 'No file uploaded';
                        }

                        $fileUrl = $record->file_url;
                        $fileName = basename($state);
                        $extension = strtolower(pathinfo($state, PATHINFO_EXTENSION));
                        
                        // Get a more descriptive filename based on document type and category
                        $descriptiveName = $record->category ? 
                            ucfirst($record->category) . '.' . $extension : 
                            ucfirst($record->document_type) . '.' . $extension;

                        // Check if file is an image
                        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                            return '<div class="flex items-center space-x-3">' .
                                   '<img src="' . $fileUrl . '" alt="' . $descriptiveName . '" class="w-36 h-36 object-cover rounded border shadow-sm" style="max-width: 150px; max-height: 150px; min-width: 150px; min-height: 150px;" />' .
                                   '<a href="' . $fileUrl . '" target="_blank" class="text-primary-600 hover:text-primary-500 underline text-sm">' . $descriptiveName . '</a>' .
                                   '</div>';
                        }
                        
                        // For non-image files, show appropriate icon
                        $iconClass = match($extension) {
                            'pdf' => 'text-red-600 fas fa-file-pdf',
                            'doc', 'docx' => 'text-blue-600 fas fa-file-word',
                            'xls', 'xlsx' => 'text-green-600 fas fa-file-excel',
                            'ppt', 'pptx' => 'text-orange-600 fas fa-file-powerpoint',
                            default => 'text-gray-600 fas fa-file'
                        };

                        return '<div class="flex items-center space-x-2">' .
                               '<i class="' . $iconClass . ' text-2xl"></i>' .
                               '<a href="' . $fileUrl . '" target="_blank" class="text-primary-600 hover:text-primary-500 underline text-sm">' . $descriptiveName . '</a>' .
                               '</div>';
                    })
                    ->html()
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('category')
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
                TextColumn::make('formatted_file_size')
                    ->label('File Size'),
                TextColumn::make('status'),
                TextColumn::make('uploaded_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('verified_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
