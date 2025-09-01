<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeTransactionResource\Pages;
use App\Models\IncomeTransaction;
use App\Models\Pilgrim;
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

class IncomeTransactionResource extends Resource
{
    protected static ?string $model = IncomeTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static UnitEnum|string|null $navigationGroup = 'Financial Management';

    protected static ?string $navigationLabel = 'Income Recording';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Transaction Information')
                    ->schema([
                        Forms\Components\Select::make('pilgrim_id')
                            ->label('Pilgrim')
                            ->required()
                            ->searchable()
                            ->options(function () {
                                return Pilgrim::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->getSearchResultsUsing(function (string $search) {
                                return Pilgrim::where('name', 'like', "%{$search}%")
                                    ->orWhere('nik', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(fn ($value): ?string => Pilgrim::find($value)?->name),
                        
                        Forms\Components\Select::make('departure_schedule_id')
                            ->label('Departure Schedule / Package')
                            ->required()
                            ->searchable()
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
                            }),
                        
                        Forms\Components\Select::make('transaction_category_id')
                            ->label('Transaction Category')
                            ->required()
                            ->options(function () {
                                return TransactionCategory::where('type', 'income')
                                    ->orderBy('category_name')
                                    ->pluck('category_name', 'id')
                                    ->toArray();
                            }),
                        
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
                
                Section::make('Payment Details')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(1)
                            ->placeholder('0.00')
                            ->rules(['gt:0']),
                        
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),
                        
                        Forms\Components\Textarea::make('note')
                            ->label('Note')
                            ->rows(3)
                            ->placeholder('Optional note about this transaction...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pilgrim.name')
                    ->label('Pilgrim')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('package_name')
                    ->label('Schedule/Package')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('departureSchedule', function (Builder $scheduleQuery) use ($search) {
                            $scheduleQuery->whereHasMorph('package', ['App\\Models\\UmrahPackage', 'App\\Models\\HalalTourPackage'], function (Builder $packageQuery) use ($search) {
                                $packageQuery->where('name', 'like', "%{$search}%");
                            });
                        });
                    })
                    ->sortable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('transactionCategory.category_name')
                    ->label('Category')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('paymentMethod.method_name')
                    ->label('Payment Method')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('account.account_name')
                    ->label('Account')
                    ->getStateUsing(function (IncomeTransaction $record): string {
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
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date()
                    ->sortable(),
                
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
                SelectFilter::make('transaction_category_id')
                    ->label('Category')
                    ->options(function () {
                        return TransactionCategory::where('type', 'income')
                            ->pluck('category_name', 'id')
                            ->toArray();
                    }),
                
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
                
                Filter::make('payment_date')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '<=', $date),
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
            ->defaultSort('payment_date', 'desc');
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
            ->select('income_transactions.*')
            ->with([
                'pilgrim',
                'departureSchedule.package',
                'transactionCategory',
                'paymentMethod',
                'account'
            ])
            ->distinct('income_transactions.id');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncomeTransactions::route('/'),
            'create' => Pages\CreateIncomeTransaction::route('/create'),
            'view' => Pages\ViewIncomeTransaction::route('/{record}'),
            'edit' => Pages\EditIncomeTransaction::route('/{record}/edit'),
        ];
    }
}