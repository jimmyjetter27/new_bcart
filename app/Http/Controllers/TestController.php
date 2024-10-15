<?php

namespace App\Http\Controllers;

use App\Contracts\ImageStorageInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\PhotoResource;
use App\Http\Resources\UserResource;
use App\Models\Photo;
use App\Models\User;
use App\Services\CloudinaryStorage;
use App\Services\ImageStorageManager;
use App\Services\LocalStorage;
use App\Services\PayStackService;
use Illuminate\Http\Request;
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
        return $imageStorage->delete('creative_uploads/cxjlf0skbpefnorp8gxo', true);    // returns 1
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
            'email' => 'jimmyjetter27@gmail.com',
            'amount' => $request->amount,
            'mobile_money' => ['phone' => '0548984119', 'provider' => 'mtn'],
            'currency' => 'GHS'
        ];
        return $service->chargeWithMobileMoney($payload);
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
}
