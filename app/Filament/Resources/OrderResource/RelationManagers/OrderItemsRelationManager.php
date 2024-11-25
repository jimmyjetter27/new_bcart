<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;


use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'photos';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Item'),
                Tables\Columns\TextColumn::make('price')->label('Price')->money('ghs', true),
            ])
            ->filters([
                // Add filters if needed
            ]);
    }

}
