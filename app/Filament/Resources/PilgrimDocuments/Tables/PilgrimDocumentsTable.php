<?php

namespace App\Filament\Resources\PilgrimDocuments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class PilgrimDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pilgrim.name')
                    ->label('Pilgrim Name')
                    ->searchable(['name', 'nik'])
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        if (!$record->pilgrim) {
                            return 'No pilgrim assigned';
                        }
                        return $record->pilgrim->name;
                    })
                    ->wrap(),
                    
                TextColumn::make('document_type')
                    ->label('Document Type')
                    ->badge()
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
                    ->color(fn (string $state): string => match ($state) {
                        'ktp', 'kk' => 'info',
                        'passport' => 'primary',
                        'visa' => 'success',
                        'marriage_certificate', 'birth_certificate' => 'warning',
                        'transfer_proof' => 'secondary',
                        'vaccine' => 'orange',
                        'ticket' => 'purple',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('files')
                    ->label('Files')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state || !is_array($state) || empty($state)) {
                            return 'No files uploaded';
                        }
                        
                        $fileCount = count($state);
                        $firstFile = $state[0];
                        $fileName = basename($firstFile);
                        $extension = strtolower(pathinfo($firstFile, PATHINFO_EXTENSION));
                        
                        if ($fileCount === 1) {
                            $fileUrl = Storage::disk('public')->url($firstFile);
                            return '<a href="' . $fileUrl . '" target="_blank" class="text-primary-600 hover:text-primary-500 underline">' . $fileName . '</a>';
                        }
                        
                        return $fileCount . ' files uploaded';
                    })
                    ->html()
                    ->searchable(),
                    
                TextColumn::make('formatted_file_size')
                    ->label('File Size'),
                    
                IconColumn::make('is_optional')
                    ->label('Optional')
                    ->boolean()
                    ->alignCenter(),
                    
                TextColumn::make('created_at')
                    ->label('Uploaded At')
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
                Action::make('view_document')
                    ->label(function ($record) {
                        if (!$record->files || !is_array($record->files) || empty($record->files)) {
                            return 'No Document';
                        }
                        $fileCount = count($record->files);
                        return $fileCount === 1 ? 'View Document' : "View Documents ({$fileCount})";
                    })
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalHeading(function ($record) {
                        $pilgrimName = $record->pilgrim ? $record->pilgrim->name : 'Unknown Pilgrim';
                        $documentType = ucfirst(str_replace('_', ' ', $record->document_type));
                        return "Documents - {$pilgrimName} ({$documentType})";
                    })
                    ->modalContent(function ($record) {
                        if (!$record->files || !is_array($record->files) || empty($record->files)) {
                            return view('filament.components.no-documents');
                        }
                        
                        return view('filament.components.document-viewer', [
                            'files' => $record->files,
                            'record' => $record
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->slideOver()
                    ->visible(function ($record) {
                        return $record->files && is_array($record->files) && !empty($record->files);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
