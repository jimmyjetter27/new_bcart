<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use Filament\Tables\Actions\Action;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Users'; // Sidebar label
//    protected static ?string $navigationGroup = 'Content Management';
    protected static ?int $navigationSort = 2;

//    protected static ?string $navigationGroup = 'Settings';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')->required(),
                Forms\Components\TextInput::make('last_name')->required(),
                Forms\Components\TextInput::make('email')->email()->required(),
                Forms\Components\TextInput::make('phone_number')->required(),
                Forms\Components\Textarea::make('description')->maxLength(500),
                Forms\Components\Select::make('type')
                    ->options([
                        'App\Models\SuperAdmin' => 'Super Admin',
                        'App\Models\Admin' => 'Admin',
                        'App\Models\Creative' => 'Creative',
                        'App\Models\RegularUser' => 'Regular User',
                    ])
//                Forms\Components\Select::make('creative_status')
//                    ->options([
//                        'available' => 'Available',
//                        'unavailable' => 'Unavailable',
//                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('last_name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('email')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('phone_number'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'App\Models\SuperAdmin' => 'Super Admin',
                        'App\Models\Admin' => 'Admin',
                        'App\Models\Creative' => 'Creative',
                        'App\Models\RegularUser' => 'Regular User',
                    ]),
                Tables\Filters\SelectFilter::make('creative_status')
                    ->options([
                        'available' => 'Available',
                        'unavailable' => 'Unavailable',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('updateCreativeStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-pencil')
                    ->visible(fn ($record) => $record->type === 'App\Models\Creative')
                    ->action(function (User $record, array $data) {
                        $record->update(['creative_status' => $data['creative_status']]);
                    })
                    ->form([
                        Forms\Components\Select::make('creative_status')
                            ->options([
                                'Verified' => 'Verified',
                                'Declined' => 'Declined'
//                                'Available' => 'Available',
//                                'Unavailable' => 'Unavailable',
                            ])
                            ->required()
                            ->label('Creative Status')
                    ])
                    ->modalHeading('Update Creative Status')
                    ->modalDescription('Select the new status for the creative.')
                    ->requiresConfirmation(),
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
