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
        if (Auth::check() || $request->isMethod('POST')) {
            return $next($request);
        }

        $key = 'page_cache_' . md5($request->fullUrl());

        if (Cache::has($key)) {
            return response(Cache::get($key));
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200) {
            Cache::put($key, $response->getContent(), 3600);
        }

        return $response;
    }
}