<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function show(string $slug, Request $request): Response
    {
        $category = Category::where("slug->fa", $slug)
            ->orWhere("slug->en", $slug)
            ->active()
            ->first();
        
        if (!$category) {
            abort(404);
        }
        
        $breadcrumb = $this->getBreadcrumb($category);
        
        return response()->view('categories.show', [
            'category'   => $category,
            'products'   => collect(),
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
            $current = $current->parent;
        }
        
        return array_reverse($breadcrumb);
    }
}