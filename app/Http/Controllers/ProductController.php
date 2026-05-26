<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display the product details.
     */
    public function show(string $slug): View
    {
        $locale = app()->getLocale();
        $column = 'slug_' . $locale;

        // Use cache to prevent repeated DB queries for the same product
        $product = \Illuminate\Support\Facades\Cache::remember("product_{$slug}_{$locale}", 3600, function () use ($slug, $column) {
            return Product::with(['category', 'images'])
                ->where($column, $slug)
                ->active()
                ->firstOrFail();
        });

        return view('products.show', [
            'product' => $product,
        ]);
    }
}