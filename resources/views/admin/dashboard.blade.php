@extends('layouts.admin')

@section('title', 'داشبورد مدیریت')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">محصولات</h3>
            <p class="text-2xl font-bold mt-2 text-indigo-600">{{ $productCount ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">دسته‌بندی‌ها</h3>
            <p class="text-2xl font-bold mt-2 text-indigo-600">{{ $categoryCount ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">کاربران</h3>
            <p class="text-2xl font-bold mt-2 text-indigo-600">{{ $userCount ?? 0 }}</p>
        </div>
    </div>
@endsection