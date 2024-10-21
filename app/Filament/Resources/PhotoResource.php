<?php

namespace App\Filament\Resources;

use App\Contracts\ImageStorageInterface;
use App\Filament\Resources\PhotoResource\Pages;
use App\Filament\Resources\PhotoResource\RelationManagers;
use App\Models\Photo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PhotoResource extends Resource
{
    protected static ?string $model = Photo::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description'),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')  // This can still be used to store locally
                    ->directory('photos')  // Or dynamically set based on the storage type
                    ->saveUploadedFileUsing(function ($record, $file) {
                        // Resolve the ImageStorageInterface based on the environment or preference
                        $imageStorage = app(ImageStorageInterface::class);

                        // Upload the file to Cloudinary or local storage
                        $result = $imageStorage->upload($file, 'photos');

                        // Save the image details in the database
                        $record->image_url = $result['secure_url'];
                        $record->image_public_id = $result['public_id'];
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('price')->sortable(),
                Tables\Columns\ImageColumn::make('image_url')->label('Photo'),
                Tables\Columns\IconColumn::make('is_approved')->true(),
                Tables\Columns\TextColumn::make('created_at')->label('Created')->dateTime(),
            ])
            ->filters([
                Tables\Filters\Filter::make('approved')
                    ->query(fn($query) => $query->where('is_approved', true)),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhotos::route('/'),
            'create' => Pages\CreatePhoto::route('/create'),
            'edit' => Pages\EditPhoto::route('/{record}/edit'),
        ];
    }
}
