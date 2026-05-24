<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\OptimizeResponse\OptimizeHtml;
use App\Http\Middleware\OptimizeResponse\AddSecurityHeaders;
use App\Http\Middleware\OptimizeResponse\AddCacheHeaders;
use App\Http\Middleware\CacheGuestResponses;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware (runs for every request)
        $middleware->append(OptimizeHtml::class);
        $middleware->append(AddSecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\DecodeSlug::class);
        
        // Web group middleware: CacheGuestResponses must run first to capture final response
        $middleware->web(prepend: [
            CacheGuestResponses::class,
            AddCacheHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();