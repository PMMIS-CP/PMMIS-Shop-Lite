<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\OptimizeResponse\OptimizeHtml;
use App\Http\Middleware\OptimizeResponse\AddSecurityHeaders;
use App\Http\Middleware\OptimizeResponse\AddCacheHeaders;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add security and optimization middleware in correct order
        $middleware->append(OptimizeHtml::class);
        $middleware->append(AddSecurityHeaders::class);
        
        // CacheGuestResponses is already added via web group
        $middleware->web(append: [
            AddCacheHeaders::class,
            \App\Http\Middleware\CacheGuestResponses::class,
        ]);
        
        $middleware->append(\App\Http\Middleware\DecodeSlug::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();