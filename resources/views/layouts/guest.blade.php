<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title . ' | ' . config('app.name', 'SIM PPTQ Ibnu Juraimi') : config('app.name', 'SIM PPTQ Ibnu Juraimi') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        <a href="#main-content" class="skip-link">Lewati ke konten utama</a>

        <main id="main-content" tabindex="-1" class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-8">
            <div class="w-full sm:max-w-md space-y-6">
                <header class="text-center">
                    <a href="{{ route('home') }}" class="inline-block">
                        <span class="text-xl font-semibold text-gray-900">PPTQ Muhammadiyah Ibnu Juraimi</span>
                    </a>
                    <p class="text-sm text-gray-600 mt-1">Sistem Presensi &amp; Monitoring Tahfidz</p>
                </header>

                <div class="w-full px-6 py-6 bg-white border border-gray-200 shadow-sm rounded-lg">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </body>
</html>
