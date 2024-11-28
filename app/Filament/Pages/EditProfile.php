<?php

namespace App\Filament\Pages;

use Exception;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Forms\Form;
use Filament\Forms\Components\Component;

class EditProfile extends BaseEditProfile
{
    use Forms\Concerns\InteractsWithForms;

    public ?array $data = [];
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

//    protected static string $view = 'filament.pages.edit-profile';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('first_name')
                ->label('First Name')
                ->required()
                ->maxLength(255),
            TextInput::make('last_name')
                ->label('Last Name')
                ->required()
                ->maxLength(255),
                TextInput::make('username')
                    ->maxLength(255),
//                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ])
            ->statePath('data');
    }
    /**
     * Define the form schema.
     */
//    protected function getFormSchema(): array
//    {
//        return [
//            TextInput::make('first_name')
//                ->label('First Name')
//                ->required()
//                ->maxLength(255),
//
//            TextInput::make('last_name')
//                ->label('Last Name')
//                ->required()
//                ->maxLength(255),
//
//            TextInput::make('email')
//                ->label(__('filament-panels::pages/auth/edit-profile.form.email.label'))
//                ->email()
//                ->required()
//                ->maxLength(255)
//                ->unique(ignoreRecord: true),
//
//            TextInput::make('password')
//                ->password()
//                ->label('New Password')
//                ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
//                ->dehydrated(fn ($state) => filled($state))
//                ->required(false)
//                ->minLength(8)
//                ->maxLength(255)
//                ->revealable()
//                ->helperText('Leave blank if you do not want to change the password.'),
//
////            Textarea::make('description')
////                ->label('Description')
////                ->maxLength(500),
//
////            FileUpload::make('profile_picture')
////                ->label('Avatar')
////                ->image()
////                ->directory('avatars')
////                ->preserveFilenames(false)
////                ->imagePreviewHeight('200')
////                ->maxSize(2048) // 2MB
////                ->imageCropAspectRatio('1:1')
////                ->imageResizeTargetWidth('300')
////                ->imageResizeTargetHeight('300')
////                ->required(false),
//        ];
//    }

    /**
     * Handle form submission.
     */
//    public function submit()
//    {
//        $data = $this->form->getState();
//
//        $user = Auth::user();
//
//        // Update user attributes
//        $user->first_name  = $data['first_name'];
//        $user->last_name   = $data['last_name'];
//        $user->description = $data['description'];
//
//        // Handle password update if provided
//        if (!empty($data['password'])) {
//            $user->password = $data['password'];
//        }
//
//        // Handle profile picture upload if provided
//        if (isset($data['profile_picture'])) {
//            // Assuming you're storing the URL; adjust if storing differently
//            $user->profile_picture_url = $data['profile_picture'];
//            // Optionally, handle deletion of the old profile picture from storage
//        }
//
//        $user->save();
//
//        // Provide feedback to the user
//        Notification::make()
//            ->title('Profile Updated')
//            ->success()
//            ->body('Your profile has been updated successfully.')
//            ->send();
//    }
}
