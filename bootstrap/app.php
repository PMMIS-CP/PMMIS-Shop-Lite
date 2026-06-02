<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\OptimizeResponse\OptimizeHtml;
use App\Http\Middleware\OptimizeResponse\AddSecurityHeaders;
use App\Http\Middleware\OptimizeResponse\AddCacheHeaders;
use App\Http\Middleware\CacheGuestResponses;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\LogRequest;
use App\Http\Middleware\DecodeSlug;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware (runs for every request)
        $middleware->append(LogRequest::class);
        $middleware->append(OptimizeHtml::class);
        $middleware->append(AddSecurityHeaders::class);
        $middleware->append(DecodeSlug::class);
        
        // Web group middleware: CacheGuestResponses must run first to capture final response
        $middleware->web(prepend: [
            CacheGuestResponses::class,
            AddCacheHeaders::class,
        ]);
        
        // Admin
        $middleware->alias([
                'admin' => AdminMiddleware::class, 
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
            $exceptions->report(function (Throwable $e) {
                Log::error('Global Error Caught:', [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]);
            });
    })->create();