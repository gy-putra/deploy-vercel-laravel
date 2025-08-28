<?php

namespace App\Filament\Resources\PilgrimDocuments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                    ->url(fn ($record) => $record->file_url)
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn ($state) => basename($state))
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
