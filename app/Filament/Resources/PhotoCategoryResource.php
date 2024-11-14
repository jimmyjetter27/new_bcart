<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoCategoryResource\Pages;
use App\Filament\Resources\PhotoCategoryResource\RelationManagers;
use App\Models\PhotoCategory;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class PhotoCategoryResource extends Resource
{
    protected static ?string $model = PhotoCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('photo_category')
                    ->required()
                    ->label('Category Name'),
                FileUpload::make('image')
                    ->label('Image')
                    ->image()
                    ->preserveFilenames()
                    ->disk('local')  // temporary local storage
                    ->saveUploadedFileUsing(fn ($file) => static::saveImage($file, 'photo_categories')),
            ]);
    }

    protected static function saveImage($file, $folder)
    {
        $imageStorage = App::make(\App\Contracts\ImageStorageInterface::class);
        $result = $imageStorage->upload($file, $folder);

        return [
            'image_public_id' => $result['public_id'] ?? null,
            'image_url' => $result['secure_url'] ?? null,
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('photo_category')->label('Category Name'),
                Tables\Columns\ImageColumn::make('image_url')->label('Image'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhotoCategories::route('/'),
            'create' => Pages\CreatePhotoCategory::route('/create'),
            'edit' => Pages\EditPhotoCategory::route('/{record}/edit'),
        ];
    }
}
