<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use Filament\Tables\Actions\Action;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

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
                Forms\Components\TextInput::make('username')->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('phone_number')->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')->maxLength(500),
                Forms\Components\Select::make('type')
                    ->options([
                        'App\Models\SuperAdmin' => 'Super Admin',
                        'App\Models\Admin' => 'Admin',
                        'App\Models\Creative' => 'Creative',
                        'App\Models\RegularUser' => 'Regular User',
                    ])->required(),
                FileUpload::make('profile_picture')
                    ->label('Avatar')
                    ->image()
                    ->directory('avatars')
                    ->preserveFilenames(false)
//                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg']) // Explicitly define MIME types
//                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
//                    ->maxSize(5000)
                    ->saveUploadedFileUsing(function ($file, $state, $set, $get) {
                        $imageStorage = app(\App\Contracts\ImageStorageInterface::class);

                        // Delete old avatar if it exists
                        $userId = $get('id');
                        $user = $userId ? User::find($userId) : null;
                        if ($user && $user->profile_picture_public_id) {
                            $imageStorage->delete('avatars/' . $user->profile_picture_public_id);
                        }

                        // Upload new avatar
                        $result = $imageStorage->upload($file, 'avatars');
                        Log::info('Cloudinary upload result:', $result);

                        // Merge metadata into the request for afterCreate
                        request()->merge([
                            'profile_picture_public_id' => $result['public_id'],
                            'profile_picture_url' => $result['secure_url'],
                        ]);

                        // Update the form state
                        $set('profile_picture_public_id', $result['public_id']);
                        $set('profile_picture_url', $result['secure_url']);

                        return $result['secure_url'];
                    }),
                TextInput::make('profile_picture_public_id')
                    ->hidden()
                    ->dehydrated(true)
                    ->default(''),

                TextInput::make('profile_picture_url')
                    ->hidden()
                    ->dehydrated(true)
                    ->default(''),
                TextInput::make('password')
                    ->password()
                    ->label('Password')
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn($record) => $record === null) // Only required for new users
                    ->visible(fn($record) => $record === null || $record !== null) // Show for both create and edit
//                    ->helperText('Leave blank if you do not want to change the password.'),


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
                Tables\Columns\ImageColumn::make('profile_picture_url')
                    ->label('Avatar')
                    ->circular()
                    ->sortable()
                    ->default(asset('/images/default-avatar.png')),
                Tables\Columns\IconColumn::make('creative_status')
                    ->label('Creative Status')
                    ->boolean()
                    ->icons([
                        'heroicon-o-check-circle' => 'Verified',
                        'heroicon-o-x-circle' => 'Declined',
                    ])
                    ->colors([
                        'success' => 'Verified',
                        'danger' => 'Declined',
                    ])
                    ->tooltip(fn($record) => $record->creative_status ?? 'Not a Creative')
//                    ->visible(fn($record) => $record->type === 'App\Models\Creative'),
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
                Action::make('viewPhotos')
                    ->icon('heroicon-o-eye')
                    ->label('')
                    ->color('primary')
                    ->modalSubmitAction(false)
                    ->tooltip('View Uploaded Photos') // Tooltip for clarity
                    ->visible(fn($record) => $record->type === 'App\Models\Creative' && $record->photos()->exists())
                    ->action(function ($record) {
                        // This action doesn't need to perform anything here
                        // The modal will handle displaying the photos
                    })
                    ->modalHeading('Uploaded Photos')
                    ->modalDescription('Browse through all photos uploaded by this creative.')
                    ->modalContent(function ($record) {
                        return view('filament.modals.user-photos', [
                            'user' => $record,
                        ]);
                    })
                    ->modalWidth('2xl'),
                Action::make('approve')
                    ->icon(fn($record) => $record->is_approved ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->label('')
                    ->action(fn($record) => $record->update(['is_approved' => !$record->is_approved]))
                    ->requiresConfirmation()
                    ->modalHeading(fn($record) => $record->is_approved ? 'Unapprove Photo' : 'Approve Photo')
                    ->modalDescription('Are you sure you want to change the approval status of this photo?')
                    ->color(fn($record) => $record->is_approved ? 'danger' : 'success')
                    ->tooltip('Verify creative')
//                    ->action(function (User $record, array $data) {
//                        if (!isset($data['profile_picture']) || !is_file($data['profile_picture'])) {
//                            throw new \Exception('Invalid avatar file provided.');
//                        }
//
//                        $imageStorage = app(\App\Contracts\ImageStorageInterface::class);
//
//                        // Delete the old avatar if it exists
//                        if ($record->profile_picture_public_id) {
//                            $imageStorage->delete('avatars/' . $record->profile_picture_public_id);
//                        }
//
//                        // Upload the new avatar
//                        $file = $data['profile_picture'];
//                        $result = $imageStorage->upload($file, 'avatars');
//
//                        // Update user's avatar info
//                        $record->update([
//                            'profile_picture_public_id' => $result['public_id'],
//                            'profile_picture_url' => $result['secure_url'],
//                        ]);
//                    })
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
                    ->requiresConfirmation()
                    ->action(function (User $record, array $data) {
//                        Log::info('Form data:', $data); // Debug the form data
                        $record->update([
                            'creative_status' => $data['creative_status'],
                        ]);
                    }),
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->label(''),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->label('')
//                    ->color('danger')
                    ->before(function ($record) {
                        // Use the ImageStorageInterface to delete the associated image
                        $imageStorage = app(\App\Contracts\ImageStorageInterface::class);

                        if ($record->profile_picture_public_id) {
                            $imageStorage->delete('avatars/' . $record->profile_picture_public_id);
                        }
                    }),
//                Tables\Actions\Action::make('updateAvatar')
//                    ->label('Update Avatar')
//                    ->icon('heroicon-o-camera')
//                    ->action(function (User $record, array $data) {
//                        $imageStorage = app(\App\Contracts\ImageStorageInterface::class);
//
//                        // Validate the uploaded file
//                        if (!isset($data['profile_picture']) || !$data['profile_picture'] instanceof \Illuminate\Http\UploadedFile) {
//                            throw new \Exception('Invalid avatar file provided.');
//                        }
//
//                        // Log uploaded file details for debugging
//                        Log::info('Uploaded File Details', [
//                            'file_name' => $data['profile_picture']->getClientOriginalName(),
//                            'file_type' => $data['profile_picture']->getMimeType(),
//                            'file_size' => $data['profile_picture']->getSize(),
//                        ]);
//
//                        // Delete the old avatar if it exists
//                        if ($record->profile_picture_public_id) {
//                            $imageStorage->delete('avatars/' . $record->profile_picture_public_id);
//                        }
//
//                        // Upload the new avatar
//                        $file = $data['profile_picture'];
//                        $result = $imageStorage->upload($file, 'avatars');
//
//                        // Log Cloudinary response for debugging
//                        Log::info('Cloudinary Upload Result', $result);
//
//                        // Update user's avatar info
//                        $record->update([
//                            'profile_picture_public_id' => $result['public_id'],
//                            'profile_picture_url' => $result['secure_url'],
//                        ]);
//
//                        // Log updated record details for debugging
//                        Log::info('Updated User Record', $record->toArray());
//                    })
//                    ->form([
//                        Forms\Components\FileUpload::make('profile_picture')
//                            ->label('Avatar')
//                            ->image()
//                            ->directory('avatars')
//                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml'])
//                            ->maxSize(5000) // 5MB max size
//                            ->required(),
//                    ])
//                    ->requiresConfirmation()
//                    ->modalHeading('Update User Avatar')
//                    ->modalDescription('Upload a new avatar for the user.'),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Action::make('bulkUpdateCreative')
                        ->label('Update Selected Creative')
                        ->icon('heroicon-o-check-circle')
                        ->action(function (User $record, array $data) {
                            $record->update(['creative_status' => $data['creative_status']]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Update Creative Status')
                        ->modalDescription('Are you sure you want to update all selected creatives?')
                        ->color('success')
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
