<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\TransactionsRelationManager;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->label('Customer')
                    ->required(),
                TextInput::make('order_number')
                    ->label('Order Number')
                    ->required()
                    ->disabled(),
                TextInput::make('total_price')
                    ->label('Total Price')
                    ->numeric()
                    ->required(),
                Select::make('transaction_status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->label('Transaction Status')
                    ->required(),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public function getCustomerNameAttribute()
    {
        return $this->customer->name ?? $this->guest_identifier ?? 'Guest';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order Number')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Price')
                    ->money('ghs', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_status')
                    ->label('Transaction Status')
                    ->sortable()
                    ->sortable([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->colors([
                        'primary' => 'pending',
                        'success' => 'completed',
                        'danger' => 'failed',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('transaction_status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('View')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Order Details')
                    ->modalWidth('lg')
                    ->modalContent(function ($record) {
                        return view('filament.modals.order-details', ['record' => $record]);
                    })
                    ->modalSubmitAction(false)
                    ->color('primary'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderItemsRelationManager::class,
            TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
//            'create' => Pages\CreateOrder::route('/create'),
//            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
