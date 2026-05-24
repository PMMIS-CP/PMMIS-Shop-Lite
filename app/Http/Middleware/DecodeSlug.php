<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DecodeSlug
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->route('slug')) {
            $slug = rawurldecode($request->route('slug')); // Single decode is enough
            $request->route()->setParameter('slug', $slug);
        }
        
        return $next($request);
    }
}