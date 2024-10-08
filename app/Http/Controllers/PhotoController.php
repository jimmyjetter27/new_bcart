<?php

namespace App\Http\Controllers;

use App\Contracts\ImageStorageInterface;
use App\Http\Resources\PhotoResource;
use App\Models\Photo;
use App\Http\Requests\StorePhotoRequest;
use App\Http\Requests\UpdatePhotoRequest;
use App\Models\PhotoTag;
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
            new Middleware('auth:sanctum', only: ['store', 'update', 'destroy'])
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
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
        $photo = [];

        foreach ($uploadedFiles as $uploadedFile) {
            // Upload the image and save it
            $result = $imageStorage->upload($uploadedFile, 'creative_uploads');

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
                    $tagModel = PhotoTag::firstOrCreate(['name' => $tag]);  // Create tag if it doesn't exist
                    $photo->tags()->attach($tagModel->id);  // Associate the tag with the photo
                }
            }

            // Attach category if provided
            if ($request->input('category')) {
                $photo->photo_categories()->attach($request->input('category'));  // Attach array of categories
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Your image(s) have been uploaded successfully. However, you'll need to  wait till they are approved to see them on your profile...",
            'data' => new PhotoResource($photo)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Photo $photo)
    {
        //
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
    public function destroy(Photo $photo)
    {
        //
    }
}
