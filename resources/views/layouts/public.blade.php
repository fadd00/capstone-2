<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PPTQ Muhammadiyah Ibnu Juraimi') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind Play CDN for standalone rendering -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50 flex flex-col min-h-screen">
    <!-- Navigation -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('home') }}" class="font-bold text-xl text-emerald-700 tracking-tight">
                        PPTQ Ibnu Juraimi
                    </a>
                </div>

                <nav class="hidden md:flex space-x-6 font-medium text-sm text-gray-700">
                    <a href="{{ route('home') }}" class="hover:text-emerald-600 transition">Beranda</a>
                    <a href="{{ route('informasi') }}" class="hover:text-emerald-600 transition">Pengumuman</a>
                    <a href="{{ route('contact') }}" class="hover:text-emerald-600 transition">Kontak</a>
                    @if (Route::has('pendaftaran'))
                        <a href="{{ route('pendaftaran') }}" class="text-emerald-600 hover:text-emerald-800 font-semibold transition">Daftar PPDB</a>
                    @endif
                </nav>

                <div class="flex items-center space-x-3">
                    @auth
                        @if (auth()->user()->hasRole('calon_santri'))
                            <a href="{{ route('user.dashboard') }}" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-md hover:bg-emerald-700 transition">
                                Dashboard Santri
                            </a>
                        @else
                            <a href="{{ url('/dashboard') }}" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-md hover:bg-emerald-700 transition">
                                Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-emerald-600 transition">
                            Masuk
                        </a>
                        @if (Route::has('pendaftaran'))
                            <a href="{{ route('pendaftaran') }}" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-md hover:bg-emerald-700 transition">
                                Daftar Sekarang
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Flash message -->
    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded text-red-700 text-sm">
                {{ session('error') }}
            </div>
        </div>
    @endif
    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded text-emerald-700 text-sm">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <!-- Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 py-8 mt-12 text-center text-sm">
        <div class="max-w-7xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} Pondok Pesantren Tahfidz Al-Qur'an Muhammadiyah Ibnu Juraimi. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
