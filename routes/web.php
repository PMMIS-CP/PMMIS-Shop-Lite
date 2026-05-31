<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Category route
Route::get('/categories/{category}', [CategoryController::class, 'show'])
    ->name('category.show');

// List Product
Route::get('/products', [ProductController::class, 'index'])
    ->name('product.index');

// Product route
Route::get('/products/{product}', [ProductController::class, 'show']) 
    ->name('product.show');

require __DIR__.'/auth.php';