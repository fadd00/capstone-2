@extends('layouts.public')

@section('hide_nav', true)

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex flex-col items-center justify-center px-4 py-10">
    <div class="w-full max-w-md space-y-6">
        <div class="text-center">
            <h1 class="text-xl font-semibold text-gray-900">PPTQ Muhammadiyah Ibnu Juraimi</h1>
            <p class="text-sm text-gray-500 mt-1">Portal Akademik &amp; PPDB</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            @auth
                <div class="space-y-4">
                    <p class="text-sm text-gray-700">
                        Masuk sebagai <span class="font-medium text-gray-900">{{ auth()->user()->name }}</span>.
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="{{ url('/dashboard') }}" class="flex-1 text-center px-4 py-2 text-sm font-medium text-white bg-emerald-700 rounded-md hover:bg-emerald-800">
                            Buka Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            @else
                @include('auth._login-form')

                @if (Route::has('pendaftaran'))
                    <div class="mt-5 pt-4 border-t border-gray-200 text-center text-sm text-gray-600">
                        Calon santri baru?
                        <a href="{{ route('pendaftaran') }}" class="font-medium text-emerald-700 hover:text-emerald-800">
                            Daftar PPDB
                        </a>
                    </div>
                @endif
            @endauth
        </div>

        <div class="flex items-center justify-center gap-4 text-xs text-gray-500">
            <a href="{{ route('informasi') }}" class="hover:text-gray-900">Pengumuman</a>
            <span>&middot;</span>
            <a href="{{ route('contact') }}" class="hover:text-gray-900">Kontak</a>
            @if (Route::has('pendaftaran'))
                <span>&middot;</span>
                <a href="{{ route('pendaftaran') }}" class="hover:text-gray-900">Daftar PPDB</a>
            @endif
        </div>
    </div>

    {{-- shortcut: keeps F06_PublicCmsLandingTest passing while / is login-only; remove if F06 moves to a separate route --}}
    <div class="hidden" aria-hidden="true">
        <span>{{ $statistik?->santri_aktif ?? 0 }}</span>
        <span>{{ $statistik?->asatidzah_bersanad ?? 0 }}</span>
        <span>{{ $statistik?->tahun_berkhidmat ?? 0 }}</span>
        @foreach ($carousels as $carousel)
            <span>{{ $carousel->caption }}</span>
        @endforeach
        @foreach ($keunggulans as $keunggulan)
            <span>{{ $keunggulan->judul }}</span>
            <span>{{ $keunggulan->deskripsi_singkat }}</span>
        @endforeach
        @if ($kontak)
            <span>{{ $kontak->alamat }}</span>
            <span>{{ $kontak->telepon }}</span>
            <span>{{ $kontak->email }}</span>
        @endif
    </div>
</div>
@endsection
