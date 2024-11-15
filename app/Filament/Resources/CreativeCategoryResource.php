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
use Illuminate\Support\Facades\Log;
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
                    ->directory('photo_categories')
                    ->saveUploadedFileUsing(function ($file, $state, $set, $get) {
                        // Use the ImageStorageInterface to handle the upload
                        $imageStorage = App::make(\App\Contracts\ImageStorageInterface::class);

                        // Retrieve the photo category using $get
                        $photoCategory = $get('photo_category');

                        if (!$photoCategory) {
                            throw new \Exception('Photo category is required to upload the image.');
                        }

                        // Generate the public ID for Cloudinary
                        $publicId = Str::slug($photoCategory);

                        Log::info("Uploading file with public ID: $publicId");

                        // Upload the image
                        $result = $imageStorage->upload($file, 'photo_categories', $publicId);

                        Log::info('Cloudinary upload result:', $result);

                        // Update the hidden fields for saving in the database
                        request()->merge([
                            'image_public_id' => $result['public_id'],
                            'image_url' => $result['secure_url'],
                        ]);

                        return $result['secure_url'];
                    }),
                TextInput::make('image_public_id')
                    ->hidden() // Hidden but bound to the model
                    ->dehydrated(true), // Ensure it is saved into the database

                TextInput::make('image_url')
                    ->hidden() // Hidden but bound to the model
                    ->dehydrated(true), // Ensure it is saved into the database
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
