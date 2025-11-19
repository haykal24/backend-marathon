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
        $allowedOriginsRaw = explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000'));
        
        // Normalize allowed origins: trim whitespace and remove trailing slash
        $allowedOrigins = array_map(function ($origin) {
            return rtrim(trim($origin), '/');
        }, $allowedOriginsRaw);
        
        $origin = $request->header('Origin');
        
        // Normalize origin: remove trailing slash if present
        $normalizedOrigin = $origin ? rtrim($origin, '/') : null;
        
        // Check if origin is allowed (case-insensitive comparison)
        $isOriginAllowed = $normalizedOrigin && in_array($normalizedOrigin, $allowedOrigins, true);

        // Handle preflight OPTIONS request
        if ($request->getMethod() === 'OPTIONS') {
            $allowedOrigin = $isOriginAllowed ? $normalizedOrigin : ($allowedOrigins[0] ?? '*');
            
            return response('', 200)
                ->header('Access-Control-Allow-Origin', $allowedOrigin)
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
        if ($isOriginAllowed) {
            return $response
                ->header('Access-Control-Allow-Origin', $normalizedOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}