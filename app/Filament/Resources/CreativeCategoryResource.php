<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreativeCategoryResource\Pages;
use App\Filament\Resources\CreativeCategoryResource\RelationManagers;
use App\Models\CreativeCategory;
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

class CreativeCategoryResource extends Resource
{
    protected static ?string $model = CreativeCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('creative_category')
                    ->label('Creative Category')
                    ->required(),
                FileUpload::make('image')
                    ->label('Image')
                    ->image()
                    ->preserveFilenames()
                    ->disk('local') // temporary storage
                    ->saveUploadedFileUsing(fn ($file) => static::saveImage($file, 'creative_categories')),
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
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->disk('cloudinary') // or 'local', depending on configuration
                    ->visibility('public'),
                Tables\Columns\TextColumn::make('creative_category')
                    ->label('Category Name')
                    ->sortable(),
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
            'index' => Pages\ListCreativeCategories::route('/'),
            'create' => Pages\CreateCreativeCategory::route('/create'),
            'edit' => Pages\EditCreativeCategory::route('/{record}/edit'),
        ];
    }
}
