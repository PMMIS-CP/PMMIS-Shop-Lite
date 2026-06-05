<?php

namespace App\Http\Controllers\Admin;

class DashboardController extends AdminController
{
    public function index()
    {
        return view('admin.dashboard', [
            'productCount' => \App\Models\Product::count(),
            'categoryCount' => \App\Models\Category::count(),
            'userCount' => \App\Models\User::count(),
        ]);
    }
}