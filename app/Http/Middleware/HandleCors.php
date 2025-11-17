<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HandleCors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get allowed origins from env (support multiple origins separated by comma)
        $allowedOrigins = explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000'));
        $origin = $request->header('Origin');

        // Handle preflight OPTIONS request
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', in_array($origin, $allowedOrigins) ? $origin : $allowedOrigins[0])
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400'); // Cache preflight for 24 hours
        }

        // Handle actual request
        $response = $next($request);

        // Skip adding CORS headers for BinaryFileResponse (file downloads)
        if ($response instanceof BinaryFileResponse) {
            return $response;
        }

        // Add CORS headers to response (only for regular Response objects)
        if (in_array($origin, $allowedOrigins)) {
            return $response
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}