<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->stateless()->user();
            $finduser = User::where('google_id', $user->id)->first();

            if ($finduser) {
                // Generate token for existing user
                $token = $finduser->createToken('auth-token')->plainTextToken;
            } else {
                // Create a new user
                $name = $user->getName();
                $split_name = explode(" ", $name);
                $newUser = User::firstOrCreate([
                    'first_name' => $split_name[0],
                    'last_name' => $split_name[1],
                    'email' => $user->getEmail(),
                    'email_verified_at' => now(),
                    'google_id' => $user->id,
                    'type' => 'App\Models\RegularUser'
                ]);
                // Generate token for new user
                $token = $newUser->createToken('auth-token')->plainTextToken;
            }

            // Redirect back to the frontend with the token (via query params or POST)
            return redirect()->to(env('FRONTEND_URL') . '/auth/callback?token=' . $token); //TODO add frontend url

        } catch (Exception $e) {
            Log::error('GoogleSignInError: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred. Please try again.']);
        }
    }

}
