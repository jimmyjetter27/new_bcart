<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrationController extends Controller
{
    public function clearDatabase()
    {
        try {
            // Run the `php artisan migrate:fresh` command
            Artisan::call('migrate:fresh --seed');

            // Get the output of the Artisan command
            $output = Artisan::output();

            // Return a success response with the Artisan command output
            return response()->json([
                'success' => true,
                'message' => 'Database migrated fresh successfully',
                'output' => $output
            ], 200);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Failed to run migration: ' . $e->getMessage(),
            ], 500);
        }
    }
}
