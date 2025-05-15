<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Filters\DateFilter;
use Illuminate\Support\Facades\Auth;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Finance';

    protected function getCurrencySymbol($currency)
    {
        return match ($currency) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'SAR' => '﷼',
            'AED' => 'د.إ',
            'KWD' => 'د.ك',
            default => '$'
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id()),
                DatePicker::make('purchase_date')
                    ->required(),
                TextInput::make('type')
                    ->required()
                    ->maxLength(100),
                TextInput::make('brand')
                    ->required()
                    ->maxLength(100),
                TextInput::make('model')
                    ->required()
                    ->maxLength(100),
                TextInput::make('shop_name')
                    ->required()
                    ->maxLength(200),
                Select::make('currency')
                    ->options([
                        'USD' => 'US Dollar ($)',
                        'EUR' => 'Euro (€)',
                        'GBP' => 'British Pound (£)',
                        'SAR' => 'Saudi Riyal (﷼)',
                        'AED' => 'UAE Dirham (د.إ)',
                        'KWD' => 'Kuwaiti Dinar (د.ك)',
                    ])
                    ->default('USD')
                    ->required()
                    ->reactive(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix(function (callable $get) {
                        $currency = $get('currency');
                        return match ($currency) {
                            'USD' => '$',
                            'EUR' => '€',
                            'GBP' => '£',
                            'SAR' => '﷼',
                            'AED' => 'د.إ',
                            'KWD' => 'د.ك',
                            default => '$'
                        };
                    }),
                SpatieMediaLibraryFileUpload::make('invoice_pdf')
                    ->collection('invoice_pdfs')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(5120) // 5MB
                    ->downloadable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('brand')
                    ->searchable(),
                TextColumn::make('model')
                    ->searchable(),
                TextColumn::make('shop_name')
                    ->searchable(),
                TextColumn::make('price')
                    ->formatStateUsing(fn (Model $record): string => "{$record->price} {$record->currency}")
                    ->sortable(),
                IconColumn::make('invoice_pdfs')
                    ->label('PDF')
                    ->boolean()
                    ->getStateUsing(fn (Model $record): bool => $record->hasMedia('invoice_pdfs')),
            ])
            ->filters([
                Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('purchase_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('purchase_date', '<=', $date),
                            );
                    }),
                SelectFilter::make('currency')
                    ->options([
                        'USD' => 'US Dollar ($)',
                        'EUR' => 'Euro (€)',
                        'GBP' => 'British Pound (£)',
                        'SAR' => 'Saudi Riyal (﷼)',
                        'AED' => 'UAE Dirham (د.إ)',
                        'KWD' => 'Kuwaiti Dinar (د.ك)',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('user_id', Auth::id())->count();
    }
}
