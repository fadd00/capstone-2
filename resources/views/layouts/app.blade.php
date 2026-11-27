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
    <body class="font-sans antialiased bg-slate-50 text-slate-900">
        <a href="#main-content" class="skip-link">Lewati ke konten utama</a>

        <div id="app-layout" class="min-h-screen flex">
            @include('layouts.navigation')

            <div id="app-shell" class="flex-1 min-w-0 flex flex-col">
                <div class="app-topbar h-16 border-b px-4 sm:px-6 lg:px-8 flex items-center justify-between sticky top-0 z-20 backdrop-blur-md text-white">
                    <div class="flex items-center gap-3 min-w-0">
                        <button id="sidebar-open-btn"
                                type="button"
                                aria-label="Buka menu"
                                aria-expanded="false"
                                aria-controls="app-sidebar"
                                class="lg:hidden p-2 rounded-xl text-blue-100 hover:text-white hover:bg-white/10 transition">
                            <svg class="h-5 w-5" aria-hidden="true" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="min-w-0">
                            <span class="hidden lg:block text-xs font-medium uppercase tracking-[0.16em] text-blue-200">Portal Digital Pesantren</span>
                            <span class="lg:hidden flex items-center gap-2 font-semibold text-sm text-white truncate">
                                <img src="{{ asset('images/logo-ibnu-juraimi.png') }}" alt="" width="300" height="247" class="logo-on-dark h-8 w-auto shrink-0">
                                PPTQ Ibnu Juraimi
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <x-dropdown id="user-account-menu" align="right" width="48">
                            <x-slot name="trigger">
                                <button type="button"
                                        popovertarget="user-account-menu"
                                        aria-controls="user-account-menu"
                                        aria-expanded="false"
                                        aria-label="{{ Auth::user()->name }}"
                                        title="{{ Auth::user()->name }}"
                                        data-popover-trigger
                                        class="inline-flex items-center justify-center p-1.5 rounded-full ring-2 ring-white/25 hover:ring-amber-400/70 text-blue-100 hover:text-white hover:bg-white/10 transition">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white/15 text-xs font-bold text-white">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="px-4 py-3 border-b border-slate-100">
                                    <p class="text-sm font-semibold text-slate-900 break-words">{{ Auth::user()->name }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500 break-all">{{ Auth::user()->email }}</p>
                                </div>
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profil') }}
                                </x-dropdown-link>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">
                                        {{ __('Keluar') }}
                                    </button>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>

                @isset($header)
                    <header class="app-page-header bg-transparent">
                        <div class="max-w-7xl mx-auto pt-8 pb-2 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main id="main-content" tabindex="-1" class="app-main flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>

        {{-- Native <dialog> Confirmation Modal (replaces blocking window.confirm) --}}
        <dialog id="app-confirm-dialog" class="app-dialog w-full sm:max-w-md" closedby="any" aria-labelledby="confirm-dialog-title">
            <form method="dialog" class="p-6 space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <h2 id="confirm-dialog-title" data-confirm-title class="text-base font-semibold text-gray-900">
                        Konfirmasi Tindakan
                    </h2>
                    <button type="submit" value="cancel" aria-label="Tutup dialog"
                            class="p-1 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100">
                        <svg class="h-4 w-4" aria-hidden="true" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <p data-confirm-message class="text-sm text-gray-600"></p>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="submit" value="cancel"
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" value="confirm" autofocus
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 transition">
                        Ya, Lanjutkan
                    </button>
                </div>
            </form>
        </dialog>
    </body>
</html>
