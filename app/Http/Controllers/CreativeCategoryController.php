<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCreativeCategoryRequest;
use App\Http\Requests\UpdateCreativeCategoryRequest;
use App\Http\Resources\CreativeCategoryResource;
use App\Models\CreativeCategory;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Policies\CreativeCategoryPolicy;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Gate;
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
    public function store(StoreCreativeCategoryRequest $request)
    {
        $policy = Gate::inspect('create', CreativeCategory::class);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

        $creative_category = CreativeCategory::create($request->validated());
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
    public function update(UpdateCreativeCategoryRequest $request, CreativeCategory $creativeCategory)
    {
        $policy = Gate::inspect('update', [CreativeCategory::class, $creativeCategory]);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

        $creativeCategory->update($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Creative category updated.',
            'data' => new CreativeCategoryResource($creativeCategory),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CreativeCategory $creativeCategory)
    {
        $policy = Gate::inspect('delete', [CreativeCategory::class, $creativeCategory]);
        if (!$policy->allowed()) {
            return response()->json([
                'success' => false,
                'message' => $policy->message()
            ], 403);
        }

        $creativeCategory->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data deleted.'
        ]);
    }
}
