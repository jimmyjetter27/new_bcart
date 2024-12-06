<?php

namespace App\Http\Controllers;

use App\Filters\FilterByMinRate;
use App\Filters\FiltersByFullName;
use App\Filters\UserInsensitiveLikeFilter;
use App\Http\Resources\UserResource;
use App\Models\Creative;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCreativeRequest;
use App\Http\Requests\UpdateCreativeRequest;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CreativeController extends Controller implements HasMiddleware
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
        $creatives = QueryBuilder::for(Creative::class)
            ->allowedFilters([
                AllowedFilter::custom('full_name', new FiltersByFullName),
                'first_name',
                'last_name',
                'username',
                'email',
                'phone_number',
                'city',
                AllowedFilter::custom('minimum_rate', new FilterByMinRate()),
                AllowedFilter::exact('creative_hire_status'),
                AllowedFilter::exact('creative_categories.id'),
            ])
            ->latest()
            ->paginate($request->per_page ?? 15);

        $creatives->load([
            'pricing',
            'paymentInfo',
            'creative_categories',
            'photos' => function ($query) {
                $query->limit(5);
            }
        ]);

        return UserResource::collection($creatives);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCreativeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Creative $creative)
    {
        $creative->load([
            'photos' => function ($query) {
                $query->paginate(15);
            }
        ]);
        return new UserResource($creative);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCreativeRequest $request, Creative $creative)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Creative $creative)
    {
        //
    }

    public function featuredCreative()
    {
        $featuredCreative = Cache::remember('featured-creative', Carbon::now()->addWeek(), function () {
            // Get a random creative each week
            return Creative::whereHas('photos')->inRandomOrder()->first();
        });

        if (!$featuredCreative) {
            return response()->json([
                'success' => false,
                'message' => 'No creatives with photos found',
            ], 404);
        }

        $featuredCreative->load([
            'creative_categories',
            'photos' => function ($query) {
                $query->limit(10); // Load only 5 photos
            }
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Featured creative',
            'data' => new UserResource($featuredCreative)
        ]);
    }

    public function featuredCreatives()
    {
        $featuredCreatives = Cache::remember('featured-creatives', Carbon::now()->addWeek(), function () {
            return Creative::inRandomOrder()->limit(10)->get();
        });

        return [
            'success' => true,
            'message' => 'Featured creatives',
            'data' => UserResource::collection($featuredCreatives)
        ];
    }


    public function search(Request $request)
    {
        $users = QueryBuilder::for(User::class)
            ->where('type', Creative::class)
            ->allowedFilters([
                AllowedFilter::custom('keyword', new UserInsensitiveLikeFilter),
            ])
            ->with([
                'pricing',
                'paymentInfo',
                'creative_categories',
                'photos' => function ($query) {
                    $query->limit(5);
                }
            ])
            ->paginate(15);

        if ($users->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No creatives found matching the criteria.',
                'data' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Search results fetched successfully.',
            'data' => UserResource::collection($users)
        ]);
    }

}
