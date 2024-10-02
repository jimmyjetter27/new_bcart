<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\Creative;
use App\Models\RegularUser;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password'))
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;
        return response()->json([
            'success' => true,
            'message' => 'User account created successfully.',
            'token' => $token,
            'data' => new UserResource($user),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();
//        if (Auth::attempt($credentials)) {
        if ($user && Hash::check($request->password, $user->password)) {
//            $user = Auth::user();

            $token = $user->createToken('auth-token')->plainTextToken;
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'data' => new UserResource($user),
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect credentials entered.'
            ], 401);
        }
    }

    public function userProfile()
    {
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'message' => 'Profile found',
            'data' => new UserResource($user)
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        $user->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated',
            'data' => new UserResource($user)
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required'
        ]);

        $current_password = $request->input('current_password');
        $new_password = $request->input('new_password');

        $user = Auth::user();

        if (!Hash::check($current_password, $user->password)) {
            return response([
                'success' => false,
                'message' => 'Incorrect password entered'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($new_password)
        ]);
        return response([
            'success' => true,
            'message' => 'Password updated.'
        ], 202);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $status = Password::sendResetLink($request->only('email'));

        // Return success or failure as a JSON response
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => __($status),
            ], 200);  // 200 OK
        } else {
            return response()->json([
                'success' => false,
                'message' => __($status),
            ], 400);  // 400 Bad Request
        }
    }

    public
    function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        // Return success or failure as a JSON response
        if ($status === Password::PASSWORD_RESET) {
            $user = User::where('email', $request->input('email'))->first();
            $token = $user->createToken('auth-token')->plainTextToken;
            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully.',
                'token' => $token,
                'data' => new UserResource($user)
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => __($status),
            ], 400); // 400 for bad request
        }
    }
}
