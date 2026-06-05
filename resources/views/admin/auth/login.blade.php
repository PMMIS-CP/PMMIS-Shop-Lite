<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ورود به پنل مدیریت</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center font-sans antialiased">

    <div class="w-full max-w-md p-8 bg-gray-800 rounded-2xl shadow-2xl border border-gray-700 mx-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">پنل مدیریت</h1>
            <p class="text-gray-400">به محیط مدیریتی خوش آمدید</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-900/30 border border-red-500/50 rounded-lg text-red-200 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-6">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">ایمیل</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all"
                    placeholder="admin@example.com">
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">رمز عبور</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all"
                    placeholder="••••••••">
            </div>

            {{-- Remember Me --}}
            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember"
                    class="w-4 h-4 rounded border-gray-700 bg-gray-900 text-orange-500 focus:ring-orange-500">
                <label for="remember" class="mr-2 text-sm text-gray-400">مرا به خاطر بسپار</label>
            </div>

            <button type="submit"
                class="w-full py-3 px-4 bg-orange-600 hover:bg-orange-500 text-white font-bold rounded-lg transition-colors duration-200 shadow-lg shadow-orange-900/20">
                ورود به پنل مدیریت
            </button>
        </form>
    </div>
</body>
</html>