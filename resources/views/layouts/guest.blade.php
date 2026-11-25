<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title . ' | ' . config('app.name', 'SIM PPTQ Ibnu Juraimi') : config('app.name', 'SIM PPTQ Ibnu Juraimi') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo-ibnu-juraimi.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased bg-slate-50">
        <a href="#main-content" class="skip-link">Lewati ke konten utama</a>

        <main id="main-content" tabindex="-1" class="guest-shell min-h-screen flex items-center justify-center px-4 py-8 sm:px-8">
            <div class="guest-panel w-full max-w-5xl overflow-hidden rounded-[2rem] bg-white shadow-xl shadow-blue-950/5 ring-1 ring-slate-200/80 lg:grid lg:grid-cols-[1fr_0.9fr]">
                <section class="guest-intro hidden flex-col justify-between bg-blue-950 p-10 text-white lg:flex xl:p-14">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo-ibnu-juraimi.png') }}" alt="" width="300" height="247" class="h-16 w-auto">

                        <span class="text-sm font-bold">PPTQ Ibnu Juraimi</span>
                    </a>
                    <div class="relative z-10 py-12">
                        <p class="mb-4 text-xs font-semibold uppercase tracking-[0.2em] text-blue-200">Sistem Informasi Digital</p>
                        <h1 class="max-w-md text-4xl font-bold leading-tight tracking-tight xl:text-5xl">Mengelola pondok, dalam satu langkah.</h1>
                        <p class="mt-5 max-w-sm text-sm leading-7 text-blue-100/80">Akses presensi, kegiatan harian, dan perkembangan tahfidz santri secara terintegrasi.</p>
                    </div>
                    <p class="text-xs text-blue-200/70">Portal Presensi &amp; Monitoring Tahfidz</p>
                    <div aria-hidden="true" class="guest-orb"></div>
                </section>

                <div class="flex flex-col justify-center p-6 sm:p-10 lg:p-12">
                    <header class="mb-8 text-center lg:text-left">
                        <img src="{{ asset('images/logo-ibnu-juraimi.png') }}" alt="Logo PPTQ Ibnu Juraimi" width="300" height="247" class="logo-on-light mx-auto mb-4 h-20 w-auto lg:hidden">
                        <a href="{{ route('home') }}" class="inline-block text-xl font-bold tracking-tight text-blue-950">PPTQ Muhammadiyah Ibnu Juraimi</a>
                        <p class="mt-2 text-sm text-slate-500">Sistem Presensi &amp; Monitoring Tahfidz</p>
                    </header>
                    <div class="w-full">
                    {{ $slot }}
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
