<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Logs DB
        if (config('app.debug')) {
            DB::listen(function ($query) {
                Log::info('SQL Query:', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ]);
            });
        }

        // Register observers once
        \App\Models\Category::observe(\App\Observers\SeoSlugObserver::class);
        \App\Models\Product::observe(\App\Observers\ProductSlugObserver::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                // ...
            ]);
        }
    }
}