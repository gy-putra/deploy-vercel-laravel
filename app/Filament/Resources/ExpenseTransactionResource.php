<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseTransactionResource\Pages;
use App\Models\ExpenseTransaction;
use App\Models\DepartureSchedule;
use App\Models\TransactionCategory;
use App\Models\PaymentMethod;
use App\Models\Account;
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

class ExpenseTransactionResource extends Resource
{
    protected static ?string $model = ExpenseTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static UnitEnum|string|null $navigationGroup = 'Financial Management';

    protected static ?string $navigationLabel = 'Expense Recording';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Expense Information')
                    ->schema([
                        Forms\Components\Select::make('departure_schedule_id')
                            ->label('Departure Schedule / Package (Optional)')
                            ->searchable()
                            ->placeholder('Select for trip-related expenses, leave empty for general operations')
                            ->options(function () {
                                return DepartureSchedule::with('package')
                                    ->where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function ($schedule) {
                                        $packageName = $schedule->package ? $schedule->package->name : 'Unknown Package';
                                        $packageType = ucfirst(str_replace('_', ' ', $schedule->package_type));
                                        $label = "{$packageType}: {$packageName} - {$schedule->departure_date->format('Y-m-d')}";
                                        return [$schedule->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->helperText('Leave empty for general operational expenses'),
                        
                        Forms\Components\Select::make('transaction_category_id')
                            ->label('Expense Category')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select expense category')
                            ->relationship(
                                name: 'transactionCategory',
                                titleAttribute: 'category_name',
                                modifyQueryUsing: fn ($query) => $query
                                    ->whereIn('type', ['expense', 'Expense', 'EXPENSE', 'EXPENSE'])
                                    ->orderBy('category_name')
                            )
                            ->rules([
                                'required',
                                'exists:transaction_categories,id'
                            ])
                            ->helperText('Only expense categories are available for selection'),
                        
                        Forms\Components\Select::make('payment_method_id')
                            ->label('Payment Method')
                            ->required()
                            ->options(function () {
                                return PaymentMethod::orderBy('method_name')
                                    ->pluck('method_name', 'id')
                                    ->toArray();
                            }),
                        
                        Forms\Components\Select::make('account_id')
                            ->label('Account')
                            ->required()
                            ->options(function () {
                                return Account::orderBy('account_name')
                                    ->get()
                                    ->mapWithKeys(function ($account) {
                                        $label = $account->account_name;
                                        if ($account->account_number) {
                                            $label .= " ({$account->account_number})";
                                        }
                                        $label .= " - {$account->account_type}";
                                        return [$account->id => $label];
                                    })
                                    ->toArray();
                            }),
                    ])
                    ->columns(2),
                
                Section::make('Expense Details')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(1)
                            ->placeholder('0.00')
                            ->rules(['gt:0']),
                        
                        Forms\Components\DatePicker::make('date')
                            ->label('Expense Date')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(3)
                            ->placeholder('Describe the expense (e.g., Garuda ticket booking, hotel accommodation, etc.)')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('transactionCategory.category_name')
                    ->label('Category')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('package_name')
                    ->label('Departure Schedule')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('departureSchedule', function (Builder $scheduleQuery) use ($search) {
                            $scheduleQuery->whereHasMorph('package', ['App\\Models\\UmrahPackage', 'App\\Models\\HalalTourPackage'], function (Builder $packageQuery) use ($search) {
                                $packageQuery->where('name', 'like', "%{$search}%");
                            });
                        });
                    })
                    ->getStateUsing(function (ExpenseTransaction $record): string {
                        return $record->package_name;
                    })
                    ->sortable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('paymentMethod.method_name')
                    ->label('Payment Method')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('account.account_name')
                    ->label('Account')
                    ->getStateUsing(function (ExpenseTransaction $record): string {
                        if (!$record->account) {
                            return 'N/A';
                        }
                        $account = $record->account;
                        $label = $account->account_name;
                        if ($account->account_number) {
                            $label .= " ({$account->account_number})";
                        }
                        return $label;
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(function (ExpenseTransaction $record): string {
                        return $record->description;
                    })
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('departure_schedule_id')
                    ->label('Departure Schedule')
                    ->options(function () {
                        return DepartureSchedule::with('package')
                            ->where('is_active', true)
                            ->get()
                            ->mapWithKeys(function ($schedule) {
                                $packageName = $schedule->package ? $schedule->package->name : 'Unknown Package';
                                $packageType = ucfirst(str_replace('_', ' ', $schedule->package_type));
                                $label = "{$packageType}: {$packageName}";
                                return [$schedule->id => $label];
                            })
                            ->toArray();
                    }),
                
                SelectFilter::make('transaction_category_id')
                    ->label('Category')
                    ->searchable()
                    ->relationship(
                        name: 'transactionCategory',
                        titleAttribute: 'category_name',
                        modifyQueryUsing: fn ($query) => $query
                            ->whereIn('type', ['expense', 'Expense', 'EXPENSE', 'EXPENSE'])
                            ->orderBy('category_name')
                    ),
                
                SelectFilter::make('payment_method_id')
                    ->label('Payment Method')
                    ->options(function () {
                        return PaymentMethod::pluck('method_name', 'id')->toArray();
                    }),
                
                SelectFilter::make('account_id')
                    ->label('Account')
                    ->options(function () {
                        return Account::pluck('account_name', 'id')->toArray();
                    }),
                
                Filter::make('expense_type')
                    ->label('Expense Type')
                    ->form([
                        Forms\Components\Select::make('type')
                            ->options([
                                'trip_related' => 'Trip Related',
                                'general_operation' => 'General Operation',
                            ])
                            ->placeholder('All Types'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['type'] === 'trip_related',
                                fn (Builder $query): Builder => $query->whereNotNull('departure_schedule_id'),
                            )
                            ->when(
                                $data['type'] === 'general_operation',
                                fn (Builder $query): Builder => $query->whereNull('departure_schedule_id'),
                            );
                    }),
                
                Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
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
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the eloquent query for the resource with proper eager loading and distinct to prevent duplicates.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select('expense_transactions.*')
            ->with([
                'departureSchedule.package',
                'transactionCategory',
                'paymentMethod',
                'account'
            ])
            ->distinct('expense_transactions.id');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenseTransactions::route('/'),
            'create' => Pages\CreateExpenseTransaction::route('/create'),
            'view' => Pages\ViewExpenseTransaction::route('/{record}'),
            'edit' => Pages\EditExpenseTransaction::route('/{record}/edit'),
        ];
    }
}