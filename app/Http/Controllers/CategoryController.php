<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function show(string $slug, Request $request): Response
    {
        // Eager load parent chain up to 10 levels to avoid N+1
        $category = Category::with('parent.parent.parent.parent.parent.parent.parent.parent.parent.parent')
            ->where(function($query) use ($slug) {
                $query->where('slug_fa', $slug)->orWhere('slug_en', $slug);
            })
            ->active()
            ->first();
        
        if (!$category) {
            abort(404);
        }
        
        $breadcrumb = $this->getBreadcrumb($category);
        
        // Load products with pagination (fix IMPR-004)
        $products = $category->products()
            ->active()
            ->orderBy('sort_order', 'asc')
            ->paginate(24);
        
        return response()->view('categories.show', [
            'category'   => $category,
            'products'   => $products,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    private function getBreadcrumb(Category $category): array
    {
        $breadcrumb = [];
        $current = $category;
        
        while ($current) {
            $breadcrumb[] = [
                'name' => $current->name,
                'url'  => $current->url,
            ];
            $current = $current->parent; // No extra query because parent is eager loaded
        }
        
        return array_reverse($breadcrumb);
    }
}