<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotaResource\Pages;
use App\Models\Quota;
use App\Models\UmrahPackage;
use App\Models\HalalTourPackage;
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
use Filament\Notifications\Notification;
use Carbon\Carbon;

class QuotaResource extends Resource
{
    protected static ?string $model = Quota::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static UnitEnum|string|null $navigationGroup = 'Paket Manejemen';

    protected static ?string $navigationLabel = 'Quota & Availability';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Package Selection')
                    ->schema([
                        Forms\Components\Select::make('package_type')
                            ->label('Package Type')
                            ->options([
                                'App\\Models\\UmrahPackage' => 'Umrah Package',
                                'App\\Models\\HalalTourPackage' => 'Halal Tour Package',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('package_id', null)),
                        
                        Forms\Components\Select::make('package_id')
                            ->label('Package')
                            ->options(function ($get) {
                                $packageType = $get('package_type');
                                if (!$packageType) {
                                    return [];
                                }
                                
                                if ($packageType === 'App\\Models\\UmrahPackage') {
                                    return UmrahPackage::query()
                                        ->where('is_active', true)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }
                                
                                if ($packageType === 'App\\Models\\HalalTourPackage') {
                                    return HalalTourPackage::query()
                                        ->where('is_active', true)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }
                                
                                return [];
                            })
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),
                
                Section::make('Quota Management')
                    ->schema([
                        Forms\Components\TextInput::make('total_quota')
                            ->label('Total Quota')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10000)
                            ->live()
                            ->afterStateUpdated(function ($set, $get, $state) {
                                $registered = $get('registered_pilgrims') ?? 0;
                                $remaining = max(0, $state - $registered);
                                $set('remaining_quota', $remaining);
                                $set('is_full', $remaining <= 0);
                            }),
                        
                        Forms\Components\TextInput::make('registered_pilgrims')
                            ->label('Registered Pilgrims')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function ($set, $get, $state) {
                                $total = $get('total_quota') ?? 0;
                                $remaining = max(0, $total - $state);
                                $set('remaining_quota', $remaining);
                                $set('is_full', $remaining <= 0);
                            }),
                        
                        Forms\Components\TextInput::make('remaining_quota')
                            ->label('Remaining Quota')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\TextInput::make('notification_threshold')
                            ->label('Notification Threshold')
                            ->helperText('Send notification when remaining quota reaches this number')
                            ->numeric()
                            ->minValue(0)
                            ->default(10),
                        
                        Forms\Components\Toggle::make('is_full')
                            ->label('Is Full')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('package.name')
                    ->label('Package Name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('package_type')
                    ->label('Package Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'App\\Models\\UmrahPackage' => 'Umrah Package',
                        'App\\Models\\HalalTourPackage' => 'Halal Tour Package',
                        default => $state,
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'App\\Models\\UmrahPackage' => 'success',
                            'App\\Models\\HalalTourPackage' => 'info',
                            default => 'gray',
                        };
                    }),
                
                Tables\Columns\TextColumn::make('total_quota')
                    ->label('Total Quota')
                    ->alignCenter()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('registered_pilgrims')
                    ->label('Registered')
                    ->alignCenter()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('remaining_quota')
                    ->label('Remaining')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color(function ($record) {
                        if ($record->is_full) return 'danger';
                        if ($record->is_almost_full) return 'warning';
                        return 'success';
                    }),
                
                Tables\Columns\TextColumn::make('utilization_percentage')
                    ->label('Utilization')
                    ->alignCenter()
                    ->suffix('%')
                    ->badge()
                    ->color(function ($record) {
                        $percentage = $record->utilization_percentage;
                        if ($percentage >= 100) return 'danger';
                        if ($percentage >= 80) return 'warning';
                        if ($percentage >= 50) return 'info';
                        return 'success';
                    }),
                
                Tables\Columns\IconColumn::make('is_full')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('notification_threshold')
                    ->label('Alert Threshold')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('package_type')
                    ->label('Package Type')
                    ->options([
                        'App\\Models\\UmrahPackage' => 'Umrah Package',
                        'App\\Models\\HalalTourPackage' => 'Halal Tour Package',
                    ])
                    ->placeholder('All Package Types'),
                
                SelectFilter::make('availability_status')
                    ->label('Availability Status')
                    ->options([
                        'available' => 'Available',
                        'almost_full' => 'Almost Full',
                        'full' => 'Full',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            function (Builder $query, string $status): Builder {
                                return match ($status) {
                                    'available' => $query->available(),
                                    'almost_full' => $query->almostFull(),
                                    'full' => $query->full(),
                                    default => $query,
                                };
                            }
                        );
                    })
                    ->placeholder('All Statuses'),
                
                Filter::make('utilization_range')
                    ->form([
                        Forms\Components\TextInput::make('utilization_from')
                            ->numeric()
                            ->suffix('%')
                            ->placeholder('Min %'),
                        Forms\Components\TextInput::make('utilization_to')
                            ->numeric()
                            ->suffix('%')
                            ->placeholder('Max %'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['utilization_from'],
                                fn (Builder $query, $percentage): Builder => 
                                    $query->whereRaw('(registered_pilgrims * 100.0 / total_quota) >= ?', [$percentage])
                            )
                            ->when(
                                $data['utilization_to'],
                                fn (Builder $query, $percentage): Builder => 
                                    $query->whereRaw('(registered_pilgrims * 100.0 / total_quota) <= ?', [$percentage])
                            );
                    }),
            ])
            ->actions([
                Actions\Action::make('increment')
                    ->label('Add Pilgrim')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->action(function (Quota $record) {
                        if (!$record->is_full) {
                            $record->incrementRegistered();
                            
                            Notification::make()
                                ->title('Pilgrim Added')
                                ->body("Registered pilgrims: {$record->fresh()->registered_pilgrims}")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Quota Full')
                                ->body('Cannot add more pilgrims. Quota is full.')
                                ->warning()
                                ->send();
                        }
                    })
                    ->visible(fn (Quota $record): bool => !$record->is_full),
                
                Actions\Action::make('decrement')
                    ->label('Remove Pilgrim')
                    ->icon('heroicon-o-minus')
                    ->color('warning')
                    ->action(function (Quota $record) {
                        if ($record->registered_pilgrims > 0) {
                            $record->decrementRegistered();
                            
                            Notification::make()
                                ->title('Pilgrim Removed')
                                ->body("Registered pilgrims: {$record->fresh()->registered_pilgrims}")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('No Pilgrims')
                                ->body('No registered pilgrims to remove.')
                                ->warning()
                                ->send();
                        }
                    })
                    ->visible(fn (Quota $record): bool => $record->registered_pilgrims > 0),
                
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
            'index' => Pages\ListQuotas::route('/'),
            'create' => Pages\CreateQuota::route('/create'),
            'view' => Pages\ViewQuota::route('/{record}'),
            'edit' => Pages\EditQuota::route('/{record}/edit'),
        ];
    }
}