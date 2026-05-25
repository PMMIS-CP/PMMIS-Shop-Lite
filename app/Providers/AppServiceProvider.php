<?php

namespace App\Providers;

use App\Models\Category;
use App\Observers\SeoSlugObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Category::observe(SeoSlugObserver::class);
        
        if ($this->app->runningInConsole()) {
            $this->commands([
                // \App\Console\Commands\MakeServiceCommand::class,
            ]);
        }
    }
}