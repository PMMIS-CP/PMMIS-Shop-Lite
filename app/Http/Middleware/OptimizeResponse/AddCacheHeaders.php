<?php

namespace App\Http\Middleware\OptimizeResponse;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AddCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Don't cache for authenticated users
        if (Auth::check()) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
            return $response;
        }

        $path = $request->path();
        
        // Cache static assets for 1 year
        if ($this->isStaticAsset($path)) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
            $response->headers->set('Pragma', 'public');
        } 
        // Cache public pages with language consideration
        elseif ($response->getStatusCode() === 200 && !$this->isAdminRoute($request)) {
            $cacheTime = config('app.cache_ttl', 300);
            $response->headers->set('Cache-Control', "public, max-age={$cacheTime}, must-revalidate");
            $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $cacheTime) . ' GMT');
            $response->headers->set('Vary', 'Accept-Language, Accept-Encoding');
        }
        
        // Add Last-Modified header for non-static pages
        if ($response->getStatusCode() === 200 && !$this->isStaticAsset($path)) {
            $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        }

        return $response;
    }

    protected function isStaticAsset(string $path): bool
    {
        $extensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'otf', 'pdf', 'xml', 'json'];
        
        foreach ($extensions as $ext) {
            if (Str::endsWith($path, '.' . $ext)) {
                return true;
            }
        }
        
        return false;
    }

    protected function isAdminRoute(Request $request): bool
    {
        // Add null coalescing to ensure array return type
        $adminPrefixes = config('app.admin_prefixes', ['admin', 'filament', 'nova', 'panel']);
        
        // Ensure it's an array (if config returns null, use default)
        if (!is_array($adminPrefixes)) {
            $adminPrefixes = ['admin', 'filament', 'nova', 'panel'];
        }
        
        $path = $request->path();
        
        foreach ($adminPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }
        
        return false;
    }
}