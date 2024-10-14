<?php

namespace App\Http\Controllers;

use App\Contracts\ImageStorageInterface;
use App\Http\Requests\StorePhotoCategoryRequest;
use App\Http\Requests\UpdatePhotoCategoryRequest;
use App\Http\Resources\PhotoCategoryResource;
use App\Models\PhotoCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;

class PhotoCategoryController extends Controller implements HasMiddleware
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
        $categoriesQuery = QueryBuilder::for(PhotoCategory::class)
            ->allowedFilters(['photo_category'])
            ->latest();

        if ($request->query('paginate') && $request->query('paginate') === 'true') {
            $categories = $categoriesQuery->paginate($request->per_page ?? 10);
        } else {
            $categories = $categoriesQuery->get();
        }

        return PhotoCategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePhotoCategoryRequest $request, ImageStorageInterface $imageStorage)
    {
        $policy = Gate::inspect('create', PhotoCategory::class);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

        if ($request->hasFile('image')) {
            $uploadedFile = $request->file('image');
            $result = $imageStorage->upload($uploadedFile, 'photo_categories', Str::slug($request->photo_category) ?? null);
        }

        $creative_category = PhotoCategory::create([
            'image_public_id' => $result['public_id'] ?? null,
            'image_url' => $result['secure_url'] ?? null,
            'photo_category' => $result->photo_category
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Photo category added.',
            'data' => new PhotoCategoryResource($creative_category)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PhotoCategory $photoCategory)
    {
        return response()->json([
            'success' => true,
            'message' => 'Photo category retrieved.',
            'data' => new PhotoCategoryResource($photoCategory)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePhotoCategoryRequest $request, PhotoCategory $photoCategory, ImageStorageInterface $imageStorage)
    {
        $policy = Gate::inspect('update', [PhotoCategory::class, $photoCategory]);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete the old image
            if ($photoCategory->image_public_id) {
                $imageStorage->delete('photo_categories/' . $photoCategory->image_public_id);
            }

            // Upload the new image
            $uploadedFile = $request->file('image');
            $result = $imageStorage->upload($uploadedFile, 'photo_categories', $request->photo_category ?? null);

            // Update the image details in the model
            $photoCategory->update([
                'image_public_id' => $result['public_id'],
                'image_url' => $result['secure_url'],
            ]);
        }

        // Update other fields
        $photoCategory->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Photo category updated.',
            'data' => new PhotoCategoryResource($photoCategory),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhotoCategory $photoCategory, ImageStorageInterface $imageStorage)
    {
        $policy = Gate::inspect('delete', [PhotoCategory::class, $photoCategory]);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

        if ($photoCategory->image_public_id) {
            $imageStorage->delete('photo_categories/' . $photoCategory->image_public_id);
        }

        $photoCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Photo category deleted.'
        ]);
    }
}
