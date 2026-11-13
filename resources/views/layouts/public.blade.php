<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PPTQ Muhammadiyah Ibnu Juraimi') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50 flex flex-col min-h-screen">
    @unless (View::hasSection('hide_nav'))
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-semibold text-base text-gray-900">
                PPTQ Ibnu Juraimi
            </a>

            <nav class="hidden sm:flex items-center space-x-6 text-sm text-gray-600">
                <a href="{{ route('home') }}" class="hover:text-gray-900">Beranda</a>
                <a href="{{ route('informasi') }}" class="hover:text-gray-900">Pengumuman</a>
                <a href="{{ route('contact') }}" class="hover:text-gray-900">Kontak</a>
                @if (Route::has('pendaftaran'))
                    <a href="{{ route('pendaftaran') }}" class="text-emerald-700 font-medium hover:text-emerald-800">Daftar PPDB</a>
                @endif
            </nav>

            <div class="flex items-center space-x-3 text-sm">
                @auth
                    <a href="{{ url('/dashboard') }}" class="px-3 py-1.5 font-medium text-white bg-emerald-700 rounded-md hover:bg-emerald-800">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Masuk</a>
                    @if (Route::has('pendaftaran'))
                        <a href="{{ route('pendaftaran') }}" class="px-3 py-1.5 font-medium text-white bg-emerald-700 rounded-md hover:bg-emerald-800">
                            Daftar PPDB
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </header>
    @endunless

    @if (session('error'))
        <div class="max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 px-4 py-3 rounded-md text-red-700 text-sm">
                {{ session('error') }}
            </div>
        </div>
    @endif
    @if (session('success'))
        <div class="max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-emerald-50 border border-emerald-200 px-4 py-3 rounded-md text-emerald-700 text-sm">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <main class="flex-grow">
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 bg-white py-5 text-center text-xs text-gray-500">
        <div class="max-w-5xl mx-auto px-4">
            &copy; {{ date('Y') }} PPTQ Muhammadiyah Ibnu Juraimi
        </div>
    </footer>
</body>
</html>
