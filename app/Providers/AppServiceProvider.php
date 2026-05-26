<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
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