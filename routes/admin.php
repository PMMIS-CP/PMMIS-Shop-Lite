<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;

Route::middleware(['auth', 'admin'])->name('admin.')->group(function () {
    
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('/dashboard', function () { 
        return view('admin.dashboard'); 
    })->name('dashboard');

    Route::resource('products', ProductController::class);
});