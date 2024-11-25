<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;


use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')->label('Transaction ID'),
                Tables\Columns\TextColumn::make('amount')->label('Amount')->money('ghs', true),
                Tables\Columns\TextColumn::make('status')->label('Status'),
                Tables\Columns\TextColumn::make('transaction_date')->label('Date')->dateTime(),
            ])
            ->filters([
                // Add filters if needed
            ]);
    }

}
