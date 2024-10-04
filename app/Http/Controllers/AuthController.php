<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreativeHiringSettingRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\Creative;
use App\Models\Hiring;
use App\Models\PaymentInformation;
use App\Models\Pricing;
use App\Models\RegularUser;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function updateCreativeDetails(CreativeHiringSettingRequest $request)
    {
        DB::beginTransaction();

        try {
            $creative = Auth::user();
            $creative->phone_number = $request->input('phone_number');
            $creative->ghana_post_gps = $request->input('ghana_post_gps');
            $creative->city = $request->input('phone_number', 'Accra');
            $creative->physical_address = $request->input('physical_address');
            $creative->description = $request->input('description');
            $creative->creative_hire_status = $request->input('creative_hire_status');


            // Step 3: Create the Pricing Details
            if ($request->has('pricing')) {
                Pricing::create([
                    'creative_id' => $creative->id,
                    'hourly_rate' => $request->input('pricing.hourly_rate'),
                    'daily_rate' => $request->input('pricing.daily_rate'),
                    'minimum_charge' => $request->input('pricing.minimum_charge'),
                    'one_day_traditional' => $request->input('pricing.one_day_traditional'),
                    'one_day_white' => $request->input('pricing.one_day_white'),
                    'one_day_white_traditional' => $request->input('pricing.one_day_white_traditional'),
                    'two_days_white_traditional' => $request->input('pricing.two_days_white_traditional'),
                    'three_days_thanksgiving' => $request->input('pricing.three_days_thanksgiving'),
                    'other_charges' => $request->input('pricing.other_charges'),
                ]);
            }

            // Step 4: Store Payment Information
            if ($request->has('payment_details')) {
                PaymentInformation::create([
                    'user_id' => $creative->id,
                    'bank_name' => $request->input('payment_details.bank_name'),
                    'bank_branch' => $request->input('payment_details.bank_branch'),
                    'bank_acc_name' => $request->input('payment_details.bank_acc_name'),
                    'bank_acc_num' => $request->input('payment_details.bank_acc_num'),
                    'momo_acc_name' => $request->input('payment_details.momo_acc_name'),
                    'momo_acc_number' => $request->input('payment_details.momo_acc_number'),
                    'preferred_payment_account' => $request->input('payment_details.preferred_payment_account', 'bank_account'),
                ]);
            }

            if ($request->has('creative_categories')) {
                $creative->categories()->sync($request->creative_categories);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated',
                'data' => new UserResource($creative)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }

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
