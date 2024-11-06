<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        $query = User::query();

        if ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->where('first_name', 'like', "%$keyword%")
                    ->orWhere('last_name', 'like', "%$keyword%")
                    ->orWhere('username', 'like', "%$keyword%")
                    ->orWhere('email', 'like', "%$keyword%")
                    ->orWhere('phone_number', 'like', "%$keyword%")
                    ->orWhere('ghana_post_gps', 'like', "%$keyword%")
                    ->orWhere('city', 'like', "%$keyword%");
            });
        }

        // Get paginated results
        $users = $query->paginate(15);

        if ($users->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No users found matching the criteria.',
                'data' => []
            ]);
        }

        // Load relationships on the paginated items
        $users->getCollection()->load([
            'pricing',
            'paymentInfo',
            'creative_categories',
            'photos' => function ($query) {
                $query->limit(5);
            }
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Search results fetched successfully.',
            'data' => UserResource::collection($users)
        ]);
    }
}
