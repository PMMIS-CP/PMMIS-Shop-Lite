<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CacheGuestResponses
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip caching for authenticated users and non-GET requests
        if (Auth::check() || !$request->isMethod('GET')) {
            return $next($request);
        }

        $key = 'page_cache_' . md5($request->fullUrl());

        // Return cached response with full headers and status
        if (Cache::has($key)) {
            $cached = Cache::get($key);
            return response($cached['content'], $cached['status'])
                ->withHeaders($cached['headers']);
        }

        $response = $next($request);

        // Cache only successful GET responses
        if ($response->getStatusCode() === 200 && !$request->ajax()) {
            Cache::put($key, [
                'content' => $response->getContent(),
                'headers' => $response->headers->all(),
                'status'  => $response->getStatusCode(),
            ], 3600);
        }

        return $response;
    }
}