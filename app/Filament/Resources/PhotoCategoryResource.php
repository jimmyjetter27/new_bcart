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
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
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
                    ->unique(ignoreRecord: true)
                    ->label('Category Name'),
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

                        // Get the record's existing image_public_id if it exists
                        $currentPublicId = $get('image_public_id');

                        if ($currentPublicId) {
                            // Attempt to delete the old image
                            $deletionSuccess = $imageStorage->delete('photo_categories/' . $currentPublicId);
                        }

                        // Generate the public ID for Cloudinary
                        $publicId = Str::slug($photoCategory);

                        // Upload the new image
                        $result = $imageStorage->upload($file, 'photo_categories', $publicId);


                        // Update the hidden fields for saving in the database
                        $set('image_public_id', $result['public_id']);
                        $set('image_url', $result['secure_url']);

                        request()->merge([
                            'image_public_id' => $result['public_id'],
                            'image_url' => $result['secure_url'],
                        ]);

                        return $result['secure_url'];
                    }),
                TextInput::make('image_public_id')
                    ->hidden()
                    ->dehydrated(true)
                    ->default(''), // Avoid null issues during form hydration
                TextInput::make('image_url')
                    ->hidden()
                    ->dehydrated(true)
                    ->default(''), // Avoid null issues during form hydration
            ]);
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
                Action::make('view_image')
                    ->icon('heroicon-o-eye')
                    ->label('')
                    ->color('gray')
                    ->modalHeading('Image Preview')
                    ->modalSubmitAction(false)
                    ->action(fn($record) => null)
                    ->modalContent(fn($record) => view('filament.modals.image-preview', [
                        'image_url' => $record->image_url,
                    ]))
                    ->disabled(fn($record) => is_null($record->image_url)),
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->label(''),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->label('')
                    ->before(function ($record) {
                        // Use the ImageStorageInterface to delete the associated image
                        $imageStorage = app(\App\Contracts\ImageStorageInterface::class);

                        if ($record->image_public_id) {
                            $imageStorage->delete('photo_categories/' . $record->image_public_id);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Bulk delete associated images
                            $imageStorage = app(\App\Contracts\ImageStorageInterface::class);

                            foreach ($records as $record) {
                                if ($record->image_public_id) {
                                    $imageStorage->delete('photo_categories/' . $record->image_public_id);
                                }
                            }
                        }),
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
