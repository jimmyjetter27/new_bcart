<?php

namespace App\Http\Controllers;

use App\Contracts\ImageStorageInterface;
use App\Filters\PhotoInsensitiveLikeFilter;
use App\Http\Resources\PhotoResource;
use App\Http\Resources\UserResource;
use App\Models\Photo;
use App\Http\Requests\StorePhotoRequest;
use App\Http\Requests\UpdatePhotoRequest;
use App\Models\PhotoTag;
use App\Models\User;
use App\Services\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
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

        if ($request->query('paginate') && $request->query('paginate') === 'true') {
            $categories = $categoriesQuery->paginate($request->per_page ?? 10);
        } else {
            $categories = $categoriesQuery
                ->limit($request->limit ?? 50)
                ->get();
        }

        // Load relationships after executing the query
        $categories->load(['creative', 'photo_categories']);

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

                // Check if image is free or not by looking out for the price key.
                $authenticated = $request->has('price') ? true : false;


//                Log::info('Uploading image with options:', [
//                    'authenticated' => $authenticated,
//                    'folder' => 'creative_uploads',
//                    'public_id' => $uploadedFile->getClientOriginalName(), // Example
//                ]);

                try {
                    $result = $imageStorage->upload($uploadedFile, 'creative_uploads', null, $authenticated);
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Image upload failed.',
                        'errors' => ['images' => [$e->getMessage()]],
                    ], 500);
                }

                $imageDimensions = getimagesize($uploadedFile->getRealPath());

                // Save the photo
                $photo = Photo::create([
                    'user_id' => auth()->id(),
                    'slug' => Str::slug($request->input('title'), '-') . '-' . uniqid(),
                    'title' => $request->input('title'),  // Optional title
                    'description' => $request->input('description'),  // Optional description
                    'price' => $request->input('price'),  // Optional price
                    'image_url' => $result['secure_url'],
                    'image_public_id' => $result['public_id'],
                    'image_width' => $imageDimensions[0],
                    'image_height' => $imageDimensions[1],
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

        $photosCollection = Photo::whereIn('id', collect($photos)->pluck('id'))
            ->with(['creative', 'photo_categories', 'tags'])
            ->get();


        return response()->json([
            'success' => true,
            'message' => "Your image(s) have been uploaded successfully. However, you'll need to wait till they are approved to see them on your profile...",
            'data' => PhotoResource::collection($photosCollection)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Photo $photo, ImageHelper $imageHelper)
    {
        $photo->load('creative');
        return response()->json([
            'success' => true,
            'message' => 'Image found.',
            'data' => new PhotoResource($photo)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePhotoRequest $request, Photo $photo, ImageStorageInterface $imageStorage)
    {
        // Check if the user is authorized to update the photo
        $policy = Gate::inspect('update', $photo);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

        // Update the photo details (title, description, price)
        $photo->title = $request->input('title', $photo->title);
        $photo->description = $request->input('description', $photo->description);
        $photo->price = $request->input('price', $photo->price);

        // If a new image is provided, upload the new image and replace the old one
        if ($request->hasFile('image')) {
            // Delete the old image from storage if it exists
            if ($photo->isStoredInCloudinary()) {
                $authenticated = $photo->price ? true : false;
                $imageStorage->delete($photo->image_public_id, $authenticated);
            } else {
                $imageStorage->delete($photo->image_public_id);
            }

            // Upload the new image
            $uploadedFile = $request->file('image');
            $result = $imageStorage->upload($uploadedFile, 'creative_uploads', null, true);

            // Get new image dimensions
            $imageDimensions = getimagesize($uploadedFile->getRealPath());

            // Update the image details in the database
            $photo->image_url = $result['secure_url'];
            $photo->image_public_id = $result['public_id'];
            $photo->image_width = $imageDimensions[0];
            $photo->image_height = $imageDimensions[1];
        }

        // Save the updated photo record
        $photo->save();

        // Handle tags (optional)
        if ($request->has('tags')) {
            $tags = $request->input('tags');
            $photo->tags()->detach();  // Detach old tags
            foreach ($tags as $tag) {
                $tagModel = PhotoTag::firstOrCreate(['name' => $tag]);
                $photo->tags()->attach($tagModel->id);
            }
        }

        // Handle category (optional)
        if ($request->input('category')) {
            $photo->photo_categories()->sync($request->input('category'));  // Sync new categories
        }

        return response()->json([
            'success' => true,
            'message' => 'Photo updated successfully.',
            'data' => new PhotoResource($photo),
        ]);
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
            $authenticated = $photo->price ? true : false;
            $imageStorage->delete('creative_uploads/' . $photo->image_public_id, $authenticated);
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

    public function relatedImages(Photo $photo)
    {
        // First, get the categories and tags of the current photo
        $photoCategories = $photo->photo_categories()->pluck('photo_category_id');
        $photoTags = $photo->tags()->pluck('photo_tag_id');

        // Query to get photos that are in the same categories or have similar tags, excluding the current photo
        $relatedImagesQuery = Photo::where('id', '!=', $photo->id) // Exclude the current photo
        ->where('is_approved', true)
            ->where(function ($query) use ($photoCategories, $photoTags) {
                $query->whereHas('photo_categories', function ($query) use ($photoCategories) {
                    $query->whereIn('photo_category_id', $photoCategories);
                });

                // Add an additional condition for tags only if tags are present
                if ($photoTags->isNotEmpty()) {
                    $query->orWhereHas('tags', function ($query) use ($photoTags) {
                        $query->whereIn('photo_tag_id', $photoTags);
                    });
                }
            });

        // Fetch the limited results
        $relatedImages = $relatedImagesQuery->limit(10)->get();

        return response()->json([
            'success' => true,
            'message' => 'Related images fetched successfully.',
            'data' => PhotoResource::collection($relatedImages),
        ]);
    }

    public function search(Request $request)
    {

        $photos = QueryBuilder::for(Photo::class)
            ->allowedFilters([
                AllowedFilter::custom('keyword', new PhotoInsensitiveLikeFilter),
            ])
            ->where('is_approved', true)
            ->paginate(15);


        if ($photos->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No photos found matching the criteria.',
                'data' => []
            ]);
        }

        $photos->getCollection()->load('creative', 'tags');

//        return response()->json([
//            'success' => true,
//            'message' => 'Search results fetched successfully.',
//            'data' => PhotoResource::collection($photos)
//        ]);

        return PhotoResource::collection($photos);
    }

    public function getUserPhotos(Request $request, $userId)
    {
        // Validate if the user exists
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Set up pagination parameters
        $perPage = $request->query('per_page', 10);

        // Get the user's approved photos with pagination
        $photos = Photo::where('user_id', $userId)
            ->where('is_approved', true)
            ->paginate($perPage);

        // Transform photos with PhotoResource without repeating creative information
        $photoData = PhotoResource::collection($photos->items())->resolve();

        // Create a structured response with creative details at the top level
        return response()->json([
            'success' => true,
            'message' => 'User photos fetched successfully.',
            'creative' => new UserResource($user),
            'photos' => [
                'data' => $photoData,
                'pagination' => [
                    'total' => $photos->total(),
                    'per_page' => $photos->perPage(),
                    'current_page' => $photos->currentPage(),
                    'last_page' => $photos->lastPage(),
                ],
            ],
        ]);
    }
    public function listPurchasedPhotos(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        $guestIdentifier = $user ? null : $request->ip() . '-' . md5($request->header('User-Agent'));

        if (!$user && !$guestIdentifier) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        $photos = Photo::whereHas('orders', function ($query) use ($user, $guestIdentifier) {
            $query->where('transaction_status', 'completed')
                ->when($user, function ($q) use ($user) {
                    $q->where('customer_id', $user->id);
                }, function ($q) use ($guestIdentifier) {
                    $q->where('guest_identifier', $guestIdentifier);
                });
        })
            ->with(['creative', 'photo_categories'])
            ->paginate();

        return PhotoResource::collection($photos);
    }
    public function downloadPhoto(Photo $photo)
    {
        $user = Auth::user();

        // Check if the image is free
        if ($photo->freeImage()) {
            return $this->downloadImage($photo);
        }

        // Check if the user is the uploader or has purchased the image
        if ($user && ($user->id === $photo->user_id || $photo->hasPurchasedPhoto($user->id))) {
            return $this->downloadImage($photo);
        }

        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to download this image.',
        ], 403);
    }

    private function downloadImage(Photo $photo)
    {
        // Retrieve the original image URL, for example from Cloudinary or local storage
        $imageUrl = $photo->image_url;

        // Check if the image URL is from a secure source and process accordingly
        return response()->json([
            'success' => true,
            'message' => 'Image ready for download.',
            'image_url' => $imageUrl,
        ]);
    }


}
