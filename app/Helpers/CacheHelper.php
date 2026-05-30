<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheHelper
{
    public static function forgetTags(array $tags): void
    {
        $store = Cache::store();
        
        if (method_exists($store, 'supportsTags') && $store->supportsTags()) {
            Cache::tags($tags)->flush();
        }
    }
    
    public static function forgetKeys(array $keys): void
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
    
    public static function forgetProductCache(int $productId): void
    {
        $keys = [
            "product_{$productId}",
            'products_list',
            'featured_products',
            'latest_products',
        ];
        
        self::forgetKeys($keys);
        self::forgetTags(['products']);
    }
}