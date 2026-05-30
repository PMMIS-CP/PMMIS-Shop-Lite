<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Display the product details.
     */
    public function show(Product $product): View
    {
        $product->setRelation('category', Cache::remember("product_cat_{$product->id}", 3600, function () use ($product) {
            return $product->category;
        }));

        $product->setRelation('images', Cache::remember("product_imgs_{$product->id}", 3600, function () use ($product) {
            return $product->images;
        }));

        return view('products.show', compact('product'));
    }
}