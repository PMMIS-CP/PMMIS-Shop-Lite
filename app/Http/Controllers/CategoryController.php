<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display the specified category.
     */
    public function show(string $slug): View
    {
        $locale = app()->getLocale();
        $column = 'slug_' . $locale;

        // Fetch category with parent chain and active status
        $category = Category::with('parent.parent.parent') // Eager load up to 3 levels
            ->where($column, $slug)
            ->active()
            ->firstOrFail();

        // Paginate active products for this category
        $products = $category->products()
            ->active()
            ->orderBy('sort_order', 'asc')
            ->paginate(24);

        return view('categories.show', [
            'category'   => $category,
            'products'   => $products,
            'breadcrumb' => $category->breadcrumb, // Uses model accessor
        ]);
    }
}