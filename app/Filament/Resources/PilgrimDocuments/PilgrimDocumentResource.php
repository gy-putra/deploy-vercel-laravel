<?php

namespace App\Filament\Resources\PilgrimDocuments;

use App\Filament\Resources\PilgrimDocuments\Pages\CreatePilgrimDocument;
use App\Filament\Resources\PilgrimDocuments\Pages\EditPilgrimDocument;
use App\Filament\Resources\PilgrimDocuments\Pages\ListPilgrimDocuments;
use App\Filament\Resources\PilgrimDocuments\Pages\ViewPilgrimDocument;
use App\Filament\Resources\PilgrimDocuments\Schemas\PilgrimDocumentForm;
use App\Filament\Resources\PilgrimDocuments\Schemas\PilgrimDocumentInfolist;
use App\Filament\Resources\PilgrimDocuments\Tables\PilgrimDocumentsTable;
use App\Models\PilgrimDocument;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PilgrimDocumentResource extends Resource
{
    protected static ?string $model = PilgrimDocument::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static UnitEnum|string|null $navigationGroup = 'Data Jamaah';

    protected static ?string $recordTitleAttribute = 'PilgrimDocument';

    public static function form(Schema $schema): Schema
    {
        return PilgrimDocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PilgrimDocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PilgrimDocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPilgrimDocuments::route('/'),
            'create' => CreatePilgrimDocument::route('/create'),
            'view' => ViewPilgrimDocument::route('/{record}'),
            'edit' => EditPilgrimDocument::route('/{record}/edit'),
        ];
    }
}
