<?php

namespace App\Filament\Resources;

use App\Contracts\ImageStorageInterface;
use App\Filament\Resources\PhotoResource\Pages;
use App\Models\Photo;
use Filament\Forms;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class PhotoResource extends Resource
{
    protected static ?string $model = Photo::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 4;

    public static function saved($record)
    {
        if ($record->is_banner) {
            \App\Models\Photo::where('id', '!=', $record->id)
                ->update(['is_banner' => false]);

            cache()->put('banner_photo', $record, now()->addDay());
        } else {
            cache()->forget('banner_photo');
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                Forms\Components\Textarea::make('description'),
                Forms\Components\TextInput::make('price')->numeric()->required(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->saveUploadedFileUsing(function ($record, $filePath) {
                        $imageStorage = app(ImageStorageInterface::class);
                        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                        $result = $imageStorage->upload($filePath, 'photos', null);
                        $record->image_url = $result['secure_url'];
                        $record->image_public_id = $result['public_id'];
                        return $record->image_url;
                    }),
                Toggle::make('is_banner')
                    ->label('Set as Banner')
                    ->helperText('Set this photo as the banner image.')
                    ->reactive()
                    ->afterStateUpdated(fn($state, $set) => $state ? $set('is_banner', true) : $set('is_banner', false))
                    ->visible(fn($record) => $record->is_approved),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('price')->sortable(),
                Tables\Columns\ImageColumn::make('image_url')->label('Photo')->url(fn($record) => $record->image_url, true)->openUrlInNewTab(),
                Tables\Columns\IconColumn::make('is_approved')->true(),
            ])
            ->filters([
                Tables\Filters\Filter::make('approved')->query(fn($query) => $query->where('is_approved', true)),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('setBanner')
                    ->icon(fn($record) => $record->is_banner ? 'heroicon-s-star' : 'heroicon-o-star')
                    ->label('')
                    ->tooltip(fn($record) => $record->is_banner ? 'Unset as Banner' : 'Set as Banner')
                    ->action(function ($record) {
                        if ($record->is_banner) {
                            // Unset the banner status
                            $record->update(['is_banner' => false]);

                            // Remove from cache
                            cache()->forget('banner_photo');
                        } else {
                            // Set this photo as the banner
                            $record->update(['is_banner' => true]);

                            // Unset banner status on other photos
                            \App\Models\Photo::where('id', '!=', $record->id)
                                ->update(['is_banner' => false]);

                            // Update the cache
                            cache()->put('banner_photo', $record, now()->addDay());
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn($record) => $record->is_banner ? 'Unset as Banner' : 'Set as Banner')
                    ->modalDescription('Are you sure you want to change the banner status of this photo?')
                    ->color(fn($record) => $record->is_banner ? 'danger' : 'success')
                    ->visible(fn($record) => $record->is_approved) // Only show for approved photos
                    ->disabled(fn($record) => !$record->is_approved),// Disable if not approved
                Action::make('approve')
                    ->icon(fn($record) => $record->is_approved ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->label('')
                    ->action(fn($record) => $record->update(['is_approved' => !$record->is_approved]))
                    ->requiresConfirmation()
                    ->modalHeading(fn($record) => $record->is_approved ? 'Unapprove Photo' : 'Approve Photo')
                    ->modalDescription('Are you sure you want to change the approval status of this photo?')
                    ->color(fn($record) => $record->is_approved ? 'danger' : 'success'),

                Action::make('viewDetails')
                    ->icon('heroicon-o-eye')
                    ->label('')
                    ->action(fn() => null)
                    ->modalHeading('Photo Details')
                    ->modalSubmitAction(false)
                    ->extraAttributes(['style' => 'text-align: left;'])
                    ->modalContent(function ($record) {
                        return view('filament.modals.photo-details', [
                            'photo' => $record,
                            'creative' => $record->creative,
                            'categories' => $record->photo_categories,
                            'created_at' => $record->created_at->format('F j, Y'),
                        ]);
                    }),

                Action::make('delete')
                    ->icon('heroicon-o-trash')
                    ->label('')
                    ->color('danger')
                    ->action(function (Photo $record) {
                        $imageStorage = app(ImageStorageInterface::class);

                        // Delete the image from storage
                        if ($record->isStoredInCloudinary()) {
                            $authenticated = $record->price ? true : false;
                            $imageStorage->delete('creative_uploads/' . $record->image_public_id, $authenticated);
                        }

                        // Detach categories and tags
                        $record->photo_categories()->detach();
                        $record->tags()->detach();

                        // Delete the record
                        $record->delete();

                    })
                    ->requiresConfirmation()
                    ->modalHeading('Delete post')
                    ->modalDescription('Are you sure you\'d like to delete this photo? This cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete it')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Action::make('bulkApprove')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn($records) => $records->each->update(['is_approved' => true]))
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Approve Photos')
                        ->modalDescription('Are you sure you want to approve all selected photos?')
                        ->color('success')
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
