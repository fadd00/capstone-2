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
                <div class="app-topbar h-16 bg-white/80 border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between sticky top-0 z-20 backdrop-blur-md">
                    <div class="flex items-center gap-3 min-w-0">
                        <button id="sidebar-open-btn"
                                type="button"
                                aria-label="Buka menu"
                                aria-expanded="false"
                                aria-controls="app-sidebar"
                                class="lg:hidden p-2 rounded-xl text-slate-600 hover:text-blue-900 hover:bg-blue-50 transition">
                            <svg class="h-5 w-5" aria-hidden="true" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="min-w-0">
                            <span class="hidden lg:block text-xs font-medium uppercase tracking-[0.16em] text-slate-400">Portal Digital Pesantren</span>
                            <span class="lg:hidden flex items-center gap-2 font-semibold text-sm text-blue-900 truncate">
                                <img src="{{ asset('images/logo-ibnu-juraimi.png') }}" alt="" width="300" height="247" class="logo-on-light h-8 w-auto shrink-0">
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
                                        data-popover-trigger
                                        class="inline-flex items-center gap-2 px-2.5 py-1.5 text-sm font-semibold rounded-xl text-slate-700 hover:text-blue-900 hover:bg-blue-50 transition">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-800">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                    <span>{{ Auth::user()->name }}</span>
                                    <svg class="ms-1 fill-current h-4 w-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
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
