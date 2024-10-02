<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
//use App\Http\Resources\UserResource;
use App\Models\Creative;
use App\Models\RegularUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
}
