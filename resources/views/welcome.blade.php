<!DOCTYPE html>
<html dir="rtl" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'پمیس شاپ') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-gray-50 text-gray-800 leading-relaxed min-h-screen flex flex-col">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex justify-between items-center py-4">
                <a href="/" class="text-2xl font-bold text-blue-600 no-underline">پمیس شاپ</a>
                <div class="flex gap-4 sm:gap-8 items-center">
                    <a href="#" class="text-gray-600 hover:text-blue-600 transition-colors duration-200 no-underline">محصولات</a>
                    <a href="#" class="text-gray-600 hover:text-blue-600 transition-colors duration-200 no-underline">دسته‌بندی‌ها</a>
                    <a href="#" class="text-gray-600 hover:text-blue-600 transition-colors duration-200 no-underline">تخفیف‌ها</a>
                    <a href="#" class="text-gray-600 hover:text-blue-600 transition-colors duration-200 no-underline">تماس با ما</a>
                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="inline-block px-5 py-2 rounded-lg font-medium bg-blue-600 text-white hover:bg-blue-700 transition-all duration-200 no-underline">
                                پنل مدیریت
                            </a>
                        @else
                            <a href="{{ url('/dashboard') }}" class="inline-block px-5 py-2 rounded-lg font-medium bg-blue-600 text-white hover:bg-blue-700 transition-all duration-200 no-underline">
                                پیشخوان
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="inline-block px-5 py-2 rounded-lg font-medium border border-gray-300 text-gray-600 hover:border-blue-600 hover:text-blue-600 transition-all duration-200 no-underline">
                            ورود
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-block px-5 py-2 rounded-lg font-medium bg-blue-600 text-white hover:bg-blue-700 transition-all duration-200 no-underline">
                                ثبت‌نام
                            </a>
                        @endif
                    @endauth
                </div>
            </nav>
        </div>
    </header>
    <main class="flex-1 flex items-center py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight mb-6 bg-linear-to-r from-gray-900 to-blue-600 bg-clip-text text-transparent">
                        خرید آسان،<br>
                        تحویل سریع
                    </h1> <!-- یبلیبلیل -->
                    <p class="text-lg text-gray-500 mb-8">
                        بهترین محصولات با بهترین قیمت‌ها. از کیفیت و اصالت کالاها مطمئن باشید.
                        تجربه خرید لذت‌بخش آنلاین با ارسال رایگان برای اولین خرید.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="inline-block px-5 py-2 rounded-lg font-medium bg-blue-600 text-white hover:bg-blue-700 transition-all duration-200 no-underline">مشاهده محصولات</a>
                        <a href="#" class="inline-block px-5 py-2 rounded-lg font-medium border border-gray-300 text-gray-600 hover:border-blue-600 hover:text-blue-600 transition-all duration-200 no-underline">درباره ما</a>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
                    <div class="text-3xl mb-4">🛍️</div>
                    <h3 class="text-xl font-semibold mb-2">تضمین کیفیت</h3>
                    <p class="text-gray-500">تمام محصولات اصلی و با گارانتی معتبر عرضه می‌شوند.</p>
                </div>
            </div>
        </div>
    </main>
    <footer class="bg-white border-t border-gray-200 py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div class="text-gray-400 text-sm">
                    &copy; {{ date('Y') }} {{ config('app.name', 'پمیس شاپ') }}. تمام حقوق محفوظ است.
                </div>
                <div class="flex gap-6">
                    <a href="#" class="text-gray-500 hover:text-blue-600 text-sm no-underline">قوانین و مقررات</a>
                    <a href="#" class="text-gray-500 hover:text-blue-600 text-sm no-underline">حریم خصوصی</a>
                    <a href="#" class="text-gray-500 hover:text-blue-600 text-sm no-underline">پشتیبانی</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>