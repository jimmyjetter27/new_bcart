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

                $token = $finduser->createToken('auth-token')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'data' => new UserResource($finduser),
                ]);

            } else {
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

                return [
                    'success' => true,
                    'token' => $newUser->createToken('auth-token')->plainTextToken,
                    'data' => new UserResource($newUser),
                ];
            }

        } catch (Exception $e) {
            Log::debug('GoogleSignInError: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ];
        }
    }

}
