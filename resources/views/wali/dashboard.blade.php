<x-app-layout title="Dashboard Wali Murid">
    <x-slot name="header">
        <h1 class="font-semibold text-lg text-gray-900 leading-tight">
            {{ __('Dashboard Wali Murid') }}
        </h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <section aria-labelledby="wali-menu-heading">
                <h2 id="wali-menu-heading" class="sr-only">Menu Monitoring</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route('wali-murid.laporan-mingguan.index') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Laporan Mingguan Tahfidz</h3>
                        <p class="text-xs text-gray-600 mt-1">Rekap mingguan setoran ziyadah dan murojaah santri.</p>
                    </a>
                    <a href="{{ route('wali-murid.riwayat-kegiatan.index') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Riwayat Kehadiran Santri</h3>
                        <p class="text-xs text-gray-600 mt-1">Rekap presensi kegiatan harian santri bersama ustadz pengampu.</p>
                    </a>
                </div>
            </section>

            <section class="bg-white border border-gray-200 rounded-lg p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Data Santri Terdaftar</h2>
                @if($children->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($children as $santri)
                            @php
                                $latestSetoran = $santri->setoranHafalan->first();
                                $latestMurojaah = $santri->murojaah->first();
                            @endphp
                            <article class="p-4 bg-gray-50 border border-gray-200 rounded-lg space-y-3">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $santri->nama }}</h3>
                                    <p class="text-xs text-gray-600">NIS: {{ $santri->nis }} | Tingkatan: {{ strtoupper($santri->tingkatan) }} | Kelas: {{ $santri->kelas ?? '-' }}</p>
                                </div>

                                <div class="p-3 bg-white rounded border border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase">Setoran Hafalan Terakhir (Ziyadah)</p>
                                    @if($latestSetoran)
                                        <p class="text-sm font-medium text-gray-900 mt-1">
                                            {{ $latestSetoran->surat }} (Juz {{ $latestSetoran->juz }}, Ayat {{ $latestSetoran->ayat }})
                                        </p>
                                        <p class="text-xs text-gray-600 mt-0.5">Nilai: {{ $latestSetoran->nilai }} | Tanggal: {{ $latestSetoran->tanggal ? \Carbon\Carbon::parse($latestSetoran->tanggal)->format('d/m/Y') : '-' }}</p>
                                    @else
                                        <p class="text-xs text-gray-500 mt-1">Belum ada catatan setoran hafalan.</p>
                                    @endif
                                </div>

                                <div class="p-3 bg-white rounded border border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase">Murojaah Terakhir</p>
                                    @if($latestMurojaah)
                                        <p class="text-sm font-medium text-gray-900 mt-1">
                                            {{ $latestMurojaah->surat }} (Juz {{ $latestMurojaah->juz }}, Ayat {{ $latestMurojaah->ayat }})
                                        </p>
                                        <p class="text-xs text-gray-600 mt-0.5">Nilai: {{ $latestMurojaah->nilai }} | Tanggal: {{ $latestMurojaah->tanggal ? \Carbon\Carbon::parse($latestMurojaah->tanggal)->format('d/m/Y') : '-' }}</p>
                                    @else
                                        <p class="text-xs text-gray-500 mt-1">Belum ada catatan murojaah.</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 text-sm">
                        Belum ada data santri yang terhubung dengan akun Wali Murid Anda. Silakan hubungi bagian administrasi pesantren.
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
