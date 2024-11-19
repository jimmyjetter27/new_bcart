<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Form;

class EditProfile extends Page
{
    protected static ?string $navigationLabel = 'Profile';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.edit-profile';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->label('Name'),
            Forms\Components\TextInput::make('email')->email()->required()->label('Email Address'),
            Forms\Components\FileUpload::make('profile_picture')
                ->label('Profile Picture')
                ->directory('profile_pictures')
                ->image()
                ->maxSize(5120)
                ->helperText('Upload a square image (e.g., 300x300px) for your profile picture.'),
            Forms\Components\TextInput::make('password')
                ->label('New Password')
                ->password()
                ->helperText('Leave blank if you don’t want to change your password.')
                ->dehydrateStateUsing(fn($state) => Hash::make($state))
                ->dehydrated(fn($state) => !empty($state)),
            Forms\Components\TextInput::make('password_confirmation')
                ->label('Confirm New Password')
                ->password()
                ->requiredWith('password')
                ->same('password'),
        ]);
    }
}
