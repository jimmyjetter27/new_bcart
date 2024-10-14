<?php

namespace App\Http\Controllers;

use App\Contracts\ImageStorageInterface;
use App\Http\Resources\PhotoResource;
use App\Models\Photo;
use App\Http\Requests\StorePhotoRequest;
use App\Http\Requests\UpdatePhotoRequest;
use App\Models\PhotoTag;
use App\Services\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;

class PhotoController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum', only: ['store', 'update', 'destroy']),
//            new Middleware('optional.auth:sanctum', only: ['show']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth('sanctum')->user();

        $categoriesQuery = QueryBuilder::for(Photo::class)
            ->allowedFilters(['title'])
            ->latest();

        // If the user is authenticated and is not an admin or super admin, apply the approved filter
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin())) {
            // Apply the filter to only show approved photos
            $categoriesQuery->where('is_approved', true);
        }

        if ($request->query('paginate') && $request->query('paginate') === 'true') {
            $categories = $categoriesQuery->paginate($request->per_page ?? 10);
        } else {
            $categories = $categoriesQuery
                ->limit($request->limit ?? 50)
                ->get();
        }

        return PhotoResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePhotoRequest $request, ImageStorageInterface $imageStorage)
    {
        $policy = Gate::inspect('create', Photo::class);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

        $uploadedFiles = $request->file('images');
        $photos = [];  // To collect all the uploaded photos

        if ($request->hasFile('images')) {
            foreach ($uploadedFiles as $uploadedFile) {
                // Upload the image and save it
                $result = $imageStorage->upload($uploadedFile, 'creative_uploads', null, true);

                // Save the photo
                $photo = Photo::create([
                    'user_id' => auth()->id(),
                    'slug' => Str::slug($request->input('title'), '-') . '-' . uniqid(),
                    'title' => $request->input('title'),  // Optional title
                    'description' => $request->input('description'),  // Optional description
                    'price' => $request->input('price'),  // Optional price
                    'image_url' => $result['secure_url'],
                    'image_public_id' => $result['public_id'],
                    'is_approved' => false
                ]);

                // Handle tags (optional)
                if ($request->has('tags')) {
                    $tags = $request->input('tags');
                    foreach ($tags as $tag) {
                        $tagModel = PhotoTag::firstOrCreate(['name' => $tag]);
                        $photo->tags()->attach($tagModel->id);
                    }
                }

                // Attach category if provided
                if ($request->input('category')) {
                    $photo->photo_categories()->attach($request->input('category'));
                }

                // Add the photo to the collection
                $photos[] = $photo;
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please provide at least one image'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Your image(s) have been uploaded successfully. However, you'll need to wait till they are approved to see them on your profile...",
            'data' => PhotoResource::collection($photos)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Photo $photo, ImageHelper $imageHelper)
    {
        return response()->json([
            'success' => true,
            'message' => 'Image found.',
            'data' => new PhotoResource($photo)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePhotoRequest $request, Photo $photo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Photo $photo, ImageStorageInterface $imageStorage)
    {
        $policy = Gate::inspect('delete', $photo);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

        // Delete the image from Cloudinary (or any other storage)
        if ($photo->isStoredInCloudinary()) {
            $imageStorage->delete('creative_uploads/'. $photo->image_public_id, true);
        }

        // Detach categories and tags before deleting the photo
        $photo->photo_categories()->detach();
        $photo->tags()->detach();

        // Delete the photo record from the database
        $photo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Photo deleted successfully.'
        ]);
    }

    public function approvePhoto(Photo $photo)
    {
        $policy = Gate::inspect('approve', Photo::class);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

        $photo->is_approved = true;
        $photo->save();

        return response()->json([
            'success' => true,
            'message' => 'Photo approved.',
            'data' => new PhotoResource($photo)
        ]);
    }
}
