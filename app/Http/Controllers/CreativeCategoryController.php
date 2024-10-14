<?php

namespace App\Http\Controllers;

use App\Contracts\ImageStorageInterface;
use App\Http\Requests\StoreCreativeCategoryRequest;
use App\Http\Requests\UpdateCreativeCategoryRequest;
use App\Http\Resources\CreativeCategoryResource;
use App\Models\CreativeCategory;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Policies\CreativeCategoryPolicy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Routing\Controllers\Middleware;

class CreativeCategoryController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', only: ['store', 'update', 'destroy']),
//            new Middleware('subscribed', except: ['store']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $categoriesQuery = QueryBuilder::for(CreativeCategory::class)
            ->allowedFilters(['creative_category'])
            ->latest();

        if ($request->query('paginate') && $request->query('paginate') === 'true') {
            $categories = $categoriesQuery->paginate($request->per_page ?? 10);
        } else {
            $categories = $categoriesQuery->get();
        }

        return CreativeCategoryResource::collection($categories);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCreativeCategoryRequest $request, ImageStorageInterface $imageStorage)
    {
        $policy = Gate::inspect('create', CreativeCategory::class);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

        if ($request->hasFile('image')) {
            $uploadedFile = $request->file('image');
            $result = $imageStorage->upload($uploadedFile, 'creative_categories', Str::slug($request->creative_category) ?? null);
        }

        $creative_category = CreativeCategory::create([
            'image_public_id' => $result['public_id'] ?? null,
            'image_url' => $result['secure_url'] ?? null,
            'creative_category' => $request->creative_category
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Creative category added.',
            'data' => new CreativeCategoryResource($creative_category)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(CreativeCategory $creativeCategory)
    {
        return response()->json([
            'success' => true,
            'message' => 'Creative category retrieved.',
            'data' => new CreativeCategoryResource($creativeCategory)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCreativeCategoryRequest $request, CreativeCategory $creativeCategory, ImageStorageInterface $imageStorage)
    {
//        return $request->all();
        // Check for permission to update
        $policy = Gate::inspect('update', [CreativeCategory::class, $creativeCategory]);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

        // Handle image upload if a new image is provided
        if ($request->hasFile('image')) {
            // Delete the existing image if it exists
            if ($creativeCategory->image_public_id) {
                $imageStorage->delete($creativeCategory->image_public_id);
            }

            // Upload the new image
            $uploadedFile = $request->file('image');
            $result = $imageStorage->upload($uploadedFile, 'creative_categories', Str::slug($request->creative_category) ?? null);

            // Update the image fields in the creative category
            $creativeCategory->image_public_id = $result['public_id'];
            $creativeCategory->image_url = $result['secure_url'];
        }

        // Update other fields
        $creativeCategory->update([
            'creative_category' => $request->creative_category ?? $creativeCategory->creative_category,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Creative category updated successfully.',
            'data' => new CreativeCategoryResource($creativeCategory),
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CreativeCategory $creativeCategory, ImageStorageInterface $imageStorage)
    {
        $policy = Gate::inspect('delete', [CreativeCategory::class, $creativeCategory]);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

        if ($creativeCategory->image_public_id) {
            $imageStorage->delete('creative_uploads/'. $creativeCategory->image_public_id, true);
        }

        $creativeCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Photo category deleted.'
        ]);
    }

    public function featuredCreativeCategories()
    {
        $featuredCreativeCategories = Cache::remember('featured-creative-categories', Carbon::now()->addWeek(), function () {
            return CreativeCategory::inRandomOrder()->limit(6)->get();
        });

        return [
            'success' => true,
            'message' => 'Featured creative categories',
            'data' => CreativeCategoryResource::collection($featuredCreativeCategories)
        ];
    }
}
