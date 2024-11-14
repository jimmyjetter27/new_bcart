<?php

namespace App\Http\Controllers;

use App\Contracts\ImageStorageInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\PhotoResource;
use App\Http\Resources\UserResource;
use App\Models\Orderable;
use App\Models\Photo;
use App\Models\User;
use App\Services\CloudinaryStorage;
use App\Services\ImageStorageManager;
use App\Services\LocalStorage;
use App\Services\PayStackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Cloudinary\Cloudinary;

class TestController extends Controller
{
    public function image()
    {

    }

    public function uploadImage(Request $request, ImageStorageInterface $imageStorage)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $uploadedFile = $request->file('image');

        // Upload the image
        $result = $imageStorage->upload($uploadedFile, 'Test/Me');

        return $result;
    }

    public function deleteImage(ImageStorageInterface $imageStorage)
    {
//        dd('wok');
//        if ($imageStorage->delete('avatars/GIcwkNKMKOSBgiMs965FHNcDRReUIzhXIQPgIGhv.jpg'))  {
//        return $imageStorage->delete('creative_uploads/cxjlf0skbpefnorp8gxo', true);    // returns 1
        return $imageStorage->delete('creative_uploads/mxyrbxd7fy34bga30z4l', false);    // returns 1
        if ($imageStorage->delete('creative_uploads/f1w6sb9ii5qpec2myl3r')) {
            return 'image deleted';
        }
        return 'image could not be deleted';
    }

    public function mailTest()
    {
        Mail::raw('Testing Gmail implementation', function ($message) {
            $message->to('jimmyjetter27@gmail.com')
                ->subject('Test Mail');
        });
    }

    public function imageTest() {
        $cld = new Cloudinary();
        $cld->imageTag('fddqxnzlxe8srtsebwi0');
        return $cld;
    }

    public function testPayment(Request $request, PayStackService $service)
    {
        $payload = [
            'email' => env('PAYSTACK_USER_EMAIL'),
            'amount' => $request->amount,
//            'mobile_money' => ['phone' => '0551234987', 'provider' => 'mtn'],
            'currency' => 'GHS'
        ];
        return $service->initializePayment($payload);
    }

    public function verifyPayment(Request $request, PayStackService $service)
    {
        $ref = $request->ref;
        return $service->verifyPayment($ref);
    }

    public function verifyUser($email)
    {
        $user = User::where('email', $email)->first();
        if ($user)
        {
            $user->email_verified_at = now();
            $user->save();
            return [
                'message' => 'email verified',
                'data' => new UserResource($user)
            ];
        }

        return 'user with '.$email.' not found';
    }

    public function deleteUser($email)
    {
        $user = User::where('email', $email)->first();
        if ($user)
        {
            $user->delete();
            return 'user deleted';
        }

        return 'user not found';
    }

    public function handleCallback(Request $request)
    {
        $token = $request->query('token');

        // Check if the token exists
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'No token provided.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Google sign-in successful!',
            'token' => $token,
        ]);
    }

    public function approvePhoto(Photo $photo)
    {
        $photo->is_approved = true;
        $photo->save();
        return new PhotoResource($photo);
    }

    public function listEnvs()
    {
//        return [
//            'db_connection' => env('DB_CONNECTION'),
//            'db_host' => env('DB_HOST'),
//            'db_port' => env('DB_PORT'),
//            'db_username' => env('DB_USERNAME'),
//            'db_password' => env('DB_PASSWORD'),
//            'google_client_id' => env('GOOGLE_CLIENT_ID'),
//            'google_client_secret' => env('GOOGLE_CLIENT_SECRET'),
//            'google_redirect_url'
//
//        ];
        return response()->json($_ENV);
    }

    public function unassignPhotos(Request $request)
    {
        $user = Auth::user();
        $photoIds = $request->input('photo_ids');

        // Validate that the user owns the photos in their orders
        $deletableOrderables = Orderable::whereIn('orderable_id', $photoIds)
            ->where('orderable_type', Photo::class)
            ->whereHas('order', fn($query) => $query->where('customer_id', $user->id))
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected photos have been unassigned.',
        ]);
    }

}
