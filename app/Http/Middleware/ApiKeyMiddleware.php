<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Api-Key');
        if (!$key || $key !== config('services.nfemis.api_key')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing API key.',
            ], 401);
        }
        return $next($request);
    }
}
