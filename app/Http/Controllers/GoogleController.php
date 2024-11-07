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
            $googleUser = Socialite::driver('google')->stateless()->user();
            // Check if the user already exists by google_id or email
            $finduser = User::where('google_id', $googleUser->id)->orWhere('email', $googleUser->getEmail())->first();

            if ($finduser) {
                // If user exists, update google_id if it's not set
                if (!$finduser->google_id) {
                    $finduser->update(['google_id' => $googleUser->id]);
                }
                // Generate token for existing user
                $token = $finduser->createToken('auth-token')->plainTextToken;
            } else {
                // Handle case where name might be a single word
                $name = $googleUser->getName();
                $split_name = explode(" ", $name);
                $first_name = $split_name[0]; // First part of the name
                $last_name = isset($split_name[1]) ? $split_name[1] : ''; // If no last name, leave empty

                // Create a new user
                $newUser = User::create([
                    'first_name' => $first_name,
                    'last_name' => $last_name, // Will be empty if no last name was provided
                    'email' => $googleUser->getEmail(),
                    'email_verified_at' => now(),
                    'google_id' => $googleUser->id,
                    'type' => 'App\Models\RegularUser'
                ]);

                // Remove the ID part from the token
                $token = explode('|', $newUser->createToken('auth-token')->plainTextToken)[1];
            }

            // Redirect back to the frontend with the token (via query params or POST)
//            return redirect()->to(env('FRONTEND_URL') . '/auth/callback?token=' . $token); // TODO Set frontend url for google
            return redirect()->to(env('FRONTEND_URL') . '?token=' . $token);

        } catch (Exception $e) {
            Log::error('GoogleSignInError: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred. Please try again.']);
        }
    }

}
