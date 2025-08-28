<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartureScheduleResource\Pages;
use App\Models\DepartureSchedule;
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
use Carbon\Carbon;

class DepartureScheduleResource extends Resource
{
    protected static ?string $model = DepartureSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static UnitEnum|string|null $navigationGroup = 'Travel Packages';

    protected static ?string $navigationLabel = 'Departure Schedule';

    protected static ?int $navigationSort = 4;

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
                
                Section::make('Departure Information')
                    ->schema([
                        Forms\Components\DatePicker::make('departure_date')
                            ->label('Departure Date')
                            ->required()
                            ->native(false)
                            ->minDate(now())
                            ->live()
                            ->afterStateUpdated(function ($set, $get, $state) {
                                // Auto-calculate return date based on package duration
                                $packageType = $get('package_type');
                                $packageId = $get('package_id');
                                
                                if ($packageType && $packageId && $state) {
                                    $package = $packageType::find($packageId);
                                    if ($package && $package->duration_days) {
                                        $returnDate = Carbon::parse($state)->addDays($package->duration_days - 1);
                                        $set('return_date', $returnDate->format('Y-m-d'));
                                    }
                                }
                            }),
                        
                        Forms\Components\TimePicker::make('departure_time')
                            ->label('Departure Time')
                            ->default('06:00')
                            ->native(false)
                            ->seconds(false),
                        
                        Forms\Components\DatePicker::make('return_date')
                            ->label('Return Date')
                            ->required()
                            ->native(false)
                            ->minDate(fn ($get) => $get('departure_date') ?: now()),
                        
                        Forms\Components\TimePicker::make('return_time')
                            ->label('Return Time')
                            ->default('18:00')
                            ->native(false)
                            ->seconds(false),
                    ])
                    ->columns(2),
                
                Section::make('Location Information')
                    ->schema([
                        Forms\Components\TextInput::make('departure_location')
                            ->label('Departure Location')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Soekarno-Hatta Airport'),
                        
                        Forms\Components\TextInput::make('return_location')
                            ->label('Return Location')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Soekarno-Hatta Airport'),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Additional information about the schedule...'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true),
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
                        'App\\Models\\UmrahPackage' => 'Umrah',
                        'App\\Models\\HalalTourPackage' => 'Halal Tour',
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
                
                Tables\Columns\TextColumn::make('formatted_departure_datetime')
                    ->label('Departure')
                    ->sortable(['departure_date', 'departure_time'])
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('formatted_return_datetime')
                    ->label('Return')
                    ->sortable(['return_date', 'return_time'])
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Duration')
                    ->suffix(' days')
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('departure_location')
                    ->label('Departure Location')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('return_location')
                    ->label('Return Location')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        if ($record->is_past) return 'Completed';
                        if ($record->is_today) return 'Today';
                        if ($record->is_upcoming) return 'Upcoming';
                        return 'Unknown';
                    })
                    ->color(function ($record) {
                        if ($record->is_past) return 'gray';
                        if ($record->is_today) return 'warning';
                        if ($record->is_upcoming) return 'success';
                        return 'gray';
                    }),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),
                
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
                
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->placeholder('All Statuses'),
                
                SelectFilter::make('schedule_status')
                    ->label('Schedule Status')
                    ->options([
                        'upcoming' => 'Upcoming',
                        'past' => 'Past',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            function (Builder $query, string $status): Builder {
                                return match ($status) {
                                    'upcoming' => $query->upcoming(),
                                    'past' => $query->past(),
                                    default => $query,
                                };
                            }
                        );
                    })
                    ->placeholder('All Schedule Statuses'),
                
                Filter::make('departure_month_year')
                    ->form([
                        Forms\Components\Select::make('month')
                            ->label('Month')
                            ->options([
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                            ])
                            ->placeholder('Select Month'),
                        Forms\Components\Select::make('year')
                            ->label('Year')
                            ->options(function () {
                                $currentYear = now()->year;
                                $years = [];
                                for ($i = $currentYear - 1; $i <= $currentYear + 3; $i++) {
                                    $years[$i] = $i;
                                }
                                return $years;
                            })
                            ->placeholder('Select Year'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['month'] && $data['year'],
                                fn (Builder $query): Builder => $query->byMonthYear($data['month'], $data['year'])
                            )
                            ->when(
                                $data['year'] && !$data['month'],
                                fn (Builder $query): Builder => $query->byYear($data['year'])
                            )
                            ->when(
                                $data['month'] && !$data['year'],
                                fn (Builder $query): Builder => $query->byMonth($data['month'])
                            );
                    }),
                
                Filter::make('departure_date_range')
                    ->form([
                        Forms\Components\DatePicker::make('departure_from')
                            ->label('Departure From')
                            ->native(false),
                        Forms\Components\DatePicker::make('departure_to')
                            ->label('Departure To')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['departure_from'] && $data['departure_to'],
                            fn (Builder $query): Builder => $query->dateBetween($data['departure_from'], $data['departure_to'])
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
            ->defaultSort('departure_date', 'asc');
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
            'index' => Pages\ListDepartureSchedules::route('/'),
            'create' => Pages\CreateDepartureSchedule::route('/create'),
            'view' => Pages\ViewDepartureSchedule::route('/{record}'),
            'edit' => Pages\EditDepartureSchedule::route('/{record}/edit'),
        ];
    }
}