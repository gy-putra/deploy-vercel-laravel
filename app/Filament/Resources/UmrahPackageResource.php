<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UmrahPackageResource\Pages;
use App\Models\UmrahPackage;
use BackedEnum;
use UnitEnum;
use Filament\Forms;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UmrahPackageResource extends Resource
{
    protected static ?string $model = UmrahPackage::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static UnitEnum|string|null $navigationGroup = 'Travel Packages';

    protected static ?string $navigationLabel = 'Umrah Package List';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Package Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->step(1000)
                            ->minValue(0),
                        
                        Forms\Components\TextInput::make('duration_days')
                            ->required()
                            ->numeric()
                            ->suffix('days')
                            ->minValue(1)
                            ->maxValue(365),
                        
                        Forms\Components\DatePicker::make('departure_date')
                            ->label('Departure Date')
                            ->native(false),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true),
                    ])
                    ->columns(2),
                
                Section::make('Package Details')
                    ->schema([
                        Forms\Components\TagsInput::make('facilities')
                            ->label('Facilities')
                            ->placeholder('Add facility and press Enter')
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('formatted_price')
                    ->label('Price')
                    ->sortable(['price'])
                    ->alignEnd(),
                
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Duration')
                    ->suffix(' days')
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date('d M Y')
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('remaining_quota')
                    ->label('Available Quota')
                    ->alignCenter()
                    ->badge()
                    ->color(function ($record) {
                        if (!$record->quota) return 'gray';
                        if ($record->quota->is_full) return 'danger';
                        if ($record->quota->is_almost_full) return 'warning';
                        return 'success';
                    }),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->placeholder('All Statuses'),
                
                Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('price_from')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('Min Price'),
                        Forms\Components\TextInput::make('price_to')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('Max Price'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn (Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn (Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            );
                    }),
                
                Filter::make('departure_date')
                    ->form([
                        Forms\Components\DatePicker::make('departure_from')
                            ->label('Departure From')
                            ->native(false),
                        Forms\Components\DatePicker::make('departure_to')
                            ->label('Departure To')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['departure_from'],
                                fn (Builder $query, $date): Builder => $query->where('departure_date', '>=', $date),
                            )
                            ->when(
                                $data['departure_to'],
                                fn (Builder $query, $date): Builder => $query->where('departure_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListUmrahPackages::route('/'),
            'create' => Pages\CreateUmrahPackage::route('/create'),
            'view' => Pages\ViewUmrahPackage::route('/{record}'),
            'edit' => Pages\EditUmrahPackage::route('/{record}/edit'),
        ];
    }
}