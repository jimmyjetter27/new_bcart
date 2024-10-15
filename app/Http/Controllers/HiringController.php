<?php

namespace App\Http\Controllers;

use App\Http\Resources\HiringResource;
use App\Models\Hiring;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHiringRequest;
use App\Http\Requests\UpdateHiringRequest;

class HiringController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHiringRequest $request)
    {
        $validatedData = $request->validated();

        $hiring = Hiring::create([
            'creative_id' => $validatedData['creative_id'],
            'regular_user_id' => auth()->id(),
            'hire_date' => $validatedData['hire_date'],
            'location' => $validatedData['location'],
            'num_days' => $validatedData['num_days'],
            'num_hours' => $validatedData['num_hours'] ?? null,
            'description' => $validatedData['description'] ?? null,
        ]);

        // Attach selected categories to the hiring
        $hiring->categories()->attach($validatedData['categories']);

        return response()->json([
            'success' => true,
            'message' => 'Creative hiring initiated successfully.',
            'data' => new HiringResource($hiring)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Hiring $hiring)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHiringRequest $request, Hiring $hiring)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hiring $hiring)
    {
        //
    }
}
