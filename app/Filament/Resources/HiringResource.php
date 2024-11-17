<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HiringResource\Pages;
use App\Filament\Resources\HiringResource\RelationManagers;
use App\Models\Hiring;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HiringResource extends Resource
{
    protected static ?string $model = Hiring::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('creative_id')
                    ->relationship('creative', 'name')
                    ->required(),
                Forms\Components\Select::make('regular_user_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('hire_date')->required(),
                Forms\Components\TextInput::make('location')->required(),
                Forms\Components\Textarea::make('description'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('creative.name'),
                Tables\Columns\TextColumn::make('customer.name'),
                Tables\Columns\TextColumn::make('hire_date')->date(),
                Tables\Columns\TextColumn::make('location'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
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
            'index' => Pages\ListHirings::route('/'),
            'create' => Pages\CreateHiring::route('/create'),
            'edit' => Pages\EditHiring::route('/{record}/edit'),
        ];
    }
}
