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
        // دسته‌بندی را به آبزرور متصل میکنیم تا قبل از ذخیره، اسلاگ را خودکار بسازد
        Category::observe(SeoSlugObserver::class);
    }
}