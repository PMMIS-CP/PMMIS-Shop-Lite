<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(): View
    {
        $products = Product::active()
            ->inStock()
            ->orderBy('sort_order')
            ->paginate(12);
            
        return view('products.index', compact('products'));
    }

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