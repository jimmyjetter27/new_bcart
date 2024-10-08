<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePhotoCategoryRequest;
use App\Http\Requests\UpdatePhotoCategoryRequest;
use App\Http\Resources\PhotoCategoryResource;
use App\Models\PhotoCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
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
    public function store(StorePhotoCategoryRequest $request)
    {
        $policy = Gate::inspect('create', PhotoCategory::class);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

        $creative_category = PhotoCategory::create($request->validated());
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
    public function update(UpdatePhotoCategoryRequest $request, PhotoCategory $photoCategory)
    {
        $policy = Gate::inspect('update', [PhotoCategory::class, $photoCategory]);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

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
    public function destroy(PhotoCategory $photoCategory)
    {
        $policy = Gate::inspect('delete', [PhotoCategory::class, $photoCategory]);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

        $photoCategory->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data deleted.'
        ]);
    }
}
