<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display the specified category.
     */
    public function show(Category $category): View
    {
        $category->load(['parent.parent']);

        $products = $category->products()
            ->active()
            ->orderBy('sort_order', 'asc')
            ->paginate(24);

        return view('categories.show', [
            'category'   => $category,
            'products'   => $products,
            'breadcrumb' => $category->breadcrumb,
        ]);
    }
}