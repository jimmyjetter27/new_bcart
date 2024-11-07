<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuggestionResource;
use App\Models\Suggestion;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['suggestion' => 'required|string']);

        $suggestion = Suggestion::create(['suggestion' => $request->input('suggestion')]);

        return response()->json([
            'success' => true,
            'message' => 'Suggestion received.',
            'data' => new SuggestionResource($suggestion)
        ]);
    }
}
