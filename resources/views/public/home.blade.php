@extends('layouts.public')

@section('content')
<div class="space-y-12">
    <!-- Carousel Hero Section -->
    @if ($carousels->isNotEmpty())
        <section class="relative bg-emerald-900 text-white overflow-hidden shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    <div>
                        <span class="inline-block px-3 py-1 text-xs font-semibold uppercase tracking-wider bg-emerald-800 text-emerald-200 rounded-full mb-4">
                            Penerimaan Santri Baru (PPDB)
                        </span>
                        <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight mb-4">
                            Mencetak Generasi Qur'ani, Berakhlak Mulia & Mandiri
                        </h1>
                        <p class="text-emerald-100 text-base sm:text-lg mb-6">
                            Bergabunglah bersama Pondok Pesantren Tahfidz Al-Qur'an Muhammadiyah Ibnu Juraimi untuk mendalami hafalan Al-Qur'an dan pendidikan karakter Islam.
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <a href="{{ route('pendaftaran') }}" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-gray-900 font-bold rounded-lg shadow-md transition">
                                Daftar Sekarang
                            </a>
                            <a href="{{ route('informasi') }}" class="px-6 py-3 bg-emerald-800 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                                Informasi Seleksi
                            </a>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @foreach ($carousels as $carousel)
                            <div class="bg-emerald-800/60 p-4 rounded-xl border border-emerald-700/50 backdrop-blur-sm">
                                @if ($carousel->image)
                                    <div class="mb-2 text-xs text-emerald-300">
                                        <span class="font-mono">{{ $carousel->image }}</span>
                                    </div>
                                @endif
                                <h3 class="text-lg font-semibold text-white">{{ $carousel->caption }}</h3>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @else
        <section class="bg-emerald-800 text-white py-16 text-center">
            <div class="max-w-3xl mx-auto px-4">
                <h1 class="text-3xl sm:text-4xl font-extrabold mb-4">Penerimaan Peserta Didik Baru (PPDB)</h1>
                <p class="text-emerald-100 mb-6">Pondok Pesantren Tahfidz Al-Qur'an Muhammadiyah Ibnu Juraimi</p>
                <a href="{{ route('pendaftaran') }}" class="inline-block px-6 py-3 bg-amber-500 hover:bg-amber-600 text-gray-900 font-bold rounded-lg shadow transition">
                    Daftar Santri Baru
                </a>
            </div>
        </section>
    @endif

    <!-- Live Statistics Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <h2 class="text-center text-xs font-semibold text-emerald-600 uppercase tracking-widest mb-2">Statistik Pesantren</h2>
            <p class="text-center text-2xl font-bold text-gray-800 mb-8">Data Terkini Pondok Kami</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-gray-200">
                <div class="pt-4 md:pt-0">
                    <div class="text-4xl font-extrabold text-emerald-600 mb-2">
                        {{ $statistik?->santri_aktif ?? 0 }}
                    </div>
                    <div class="text-sm font-medium text-gray-600">Santri Aktif Belajar</div>
                </div>
                <div class="pt-4 md:pt-0">
                    <div class="text-4xl font-extrabold text-emerald-600 mb-2">
                        {{ $statistik?->asatidzah_bersanad ?? 0 }}
                    </div>
                    <div class="text-sm font-medium text-gray-600">Asatidzah Bersanad</div>
                </div>
                <div class="pt-4 md:pt-0">
                    <div class="text-4xl font-extrabold text-emerald-600 mb-2">
                        {{ $statistik?->tahun_berkhidmat ?? 0 }}
                    </div>
                    <div class="text-sm font-medium text-gray-600">Tahun Berkhidmat</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Keunggulan Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <h2 class="text-xs font-semibold text-emerald-600 uppercase tracking-widest mb-2">Keunggulan</h2>
            <p class="text-3xl font-bold text-gray-900">Mengapa Memilih Ibnu Juraimi?</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse ($keunggulans as $keunggulan)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $keunggulan->judul }}</h3>
                    <p class="text-gray-600 text-sm mb-3">{{ $keunggulan->deskripsi_singkat }}</p>
                    @if ($keunggulan->deskripsi_detail)
                        <p class="text-gray-500 text-xs leading-relaxed border-t border-gray-100 pt-3">{{ $keunggulan->deskripsi_detail }}</p>
                    @endif
                </div>
            @empty
                <div class="col-span-3 text-center py-8 text-gray-500">
                    Belum ada data keunggulan pesantren.
                </div>
            @endforelse
        </div>
    </section>

    <!-- Official Contact Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-emerald-50 rounded-2xl p-8 border border-emerald-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Kontak & Sekretariat PPDB</h2>
                    <p class="text-gray-600 text-sm mb-6">
                        Informasi lebih lanjut tentang alur pendaftaran, biaya, dan program tahfidz dapat dikonsultasikan melalui kontak berikut:
                    </p>
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-start">
                            <dt class="font-semibold text-gray-700 w-24">Alamat:</dt>
                            <dd class="text-gray-600">{{ $kontak?->alamat ?? 'Alamat belum diatur' }}</dd>
                        </div>
                        <div class="flex items-center">
                            <dt class="font-semibold text-gray-700 w-24">Telepon:</dt>
                            <dd class="text-gray-600 font-mono">{{ $kontak?->telepon ?? '-' }}</dd>
                        </div>
                        <div class="flex items-center">
                            <dt class="font-semibold text-gray-700 w-24">Email:</dt>
                            <dd class="text-gray-600">{{ $kontak?->email ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
                <div class="text-center md:text-right">
                    <a href="{{ route('contact') }}" class="inline-block px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg shadow transition">
                        Hubungi Kami
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
