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
use Filament\Tables\Actions\Action;
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
                    ->saveUploadedFileUsing(function ($record, $filePath) {
                        // Resolve the ImageStorageInterface (Cloudinary or local storage)
                        $imageStorage = app(ImageStorageInterface::class);

                        // Extract file extension from the file path using native PHP functions
                        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

                        // Upload the file to Cloudinary or local storage
                        $result = $imageStorage->upload($filePath, 'photos', null);

                        // Save the image details in the database
                        $record->image_url = $result['secure_url'];
                        $record->image_public_id = $result['public_id'];

                        // Return the file URL to be saved
                        return $record->image_url;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('price')->sortable(),
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Photo')
                    ->url(fn($record) => $record->image_url, true) // Opens in a new tab
                    ->openUrlInNewTab(),
                Tables\Columns\IconColumn::make('is_approved')->true(),
            ])
            ->filters([
                Tables\Filters\Filter::make('approved')
                    ->query(fn($query) => $query->where('is_approved', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('approve')
                    ->label(fn($record) => $record->is_approved ? 'Unapprove' : 'Approve')
                    ->icon(fn($record) => $record->is_approved ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->action(fn($record) => $record->update(['is_approved' => !$record->is_approved]))
                    ->requiresConfirmation()
                    ->modalHeading(fn($record) => $record->is_approved ? 'Unapprove Photo' : 'Approve Photo')
                    ->modalSubheading('Are you sure you want to change the approval status of this photo?')
                    ->color(fn($record) => $record->is_approved ? 'danger' : 'success'),

                Action::make('viewDetails')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->action(fn () => null) // Set to do nothing on submit, making it view-only
                    ->modalHeading('Photo Details')
                    ->modalActions([]) // Sets an empty array for modal actions to remove buttons
                    ->extraAttributes([
                        'style' => 'text-align: left;', // Optional: to ensure content aligns properly
                    ])
                    ->modalContent(function ($record) {
                        return view('filament.photo-details', [
                            'photo' => $record,
                            'creative' => $record->creative,
                            'categories' => $record->photo_categories,
                            'created_at' => $record->created_at->format('F j, Y'),
                        ]);
                    }),
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
