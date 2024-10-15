<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuggestionResource;
use App\Models\SuggestUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuggestUploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['suggestion' => 'required|string']);

        $suggestion = SuggestUpload::create([
            'user_id' => $request->user()->id,
            'suggestion' => $request->input('suggestion')
        ]);

        return response()->json([
           'success' => true,
           'message' => 'Suggestion received.',
           'data' => new SuggestionResource($suggestion)
        ]);
    }
}
