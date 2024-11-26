<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OptionalAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set the default guard to 'sanctum'
        Auth::shouldUse('sanctum');

        // Attempt to authenticate the user
        $user = Auth::user(); // This uses the 'sanctum' guard now

        // Proceed with the request regardless of authentication status
        return $next($request);
    }
}
