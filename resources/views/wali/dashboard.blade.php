<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Wali Murid') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Selamat Datang, {{ auth()->user()->name }}</h3>
                <p class="text-sm text-gray-600 mb-6">Portal Monitoring Akademik dan Tahfidz Santri PPTQ Ibnu Juraimi</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <a href="{{ route('wali-murid.laporan-mingguan.index') }}" class="p-6 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition">
                        <h4 class="font-bold text-indigo-900 text-lg mb-1">Laporan Mingguan Tahfidz</h4>
                        <p class="text-sm text-indigo-700">Lihat rekap mingguan setoran ziyadah hafalan baru dan murojaah santri.</p>
                    </a>
                    <a href="{{ route('wali-murid.riwayat-kegiatan.index') }}" class="p-6 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                        <h4 class="font-bold text-green-900 text-lg mb-1">Riwayat Kehadiran Santri</h4>
                        <p class="text-sm text-green-700">Pantau rekapitulasi presensi kegiatan harian santri bersama ustadz pengampu.</p>
                    </a>
                </div>

                <div class="border-t pt-4">
                    <h4 class="font-semibold text-gray-800 mb-4 text-lg">Data Santri Terdaftar</h4>
                    @if($children->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($children as $santri)
                                @php
                                    $latestSetoran = $santri->setoranHafalan->first();
                                    $latestMurojaah = $santri->murojaah->first();
                                @endphp
                                <div class="p-5 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h5 class="font-bold text-gray-900 text-lg">{{ $santri->nama }}</h5>
                                            <p class="text-xs text-gray-500">NIS: {{ $santri->nis }} | Tingkatan: {{ strtoupper($santri->tingkatan) }} | Kelas: {{ $santri->kelas ?? '-' }}</p>
                                        </div>
                                    </div>

                                    <div class="space-y-3 mt-4 border-t pt-3">
                                        <div class="p-3 bg-white rounded border border-gray-100">
                                            <p class="text-xs font-semibold text-indigo-600 uppercase">Setoran Hafalan Terakhir (Ziyadah)</p>
                                            @if($latestSetoran)
                                                <p class="text-sm font-medium text-gray-800 mt-1">
                                                    {{ $latestSetoran->surat }} (Juz {{ $latestSetoran->juz }}, Ayat {{ $latestSetoran->ayat }})
                                                </p>
                                                <p class="text-xs text-gray-500 mt-0.5">Nilai: <span class="font-semibold text-gray-700">{{ $latestSetoran->nilai }}</span> | Tanggal: {{ $latestSetoran->tanggal ? \Carbon\Carbon::parse($latestSetoran->tanggal)->format('d/m/Y') : '-' }}</p>
                                            @else
                                                <p class="text-xs text-gray-400 mt-1">Belum ada catatan setoran hafalan.</p>
                                            @endif
                                        </div>

                                        <div class="p-3 bg-white rounded border border-gray-100">
                                            <p class="text-xs font-semibold text-green-600 uppercase">Murojaah Terakhir</p>
                                            @if($latestMurojaah)
                                                <p class="text-sm font-medium text-gray-800 mt-1">
                                                    {{ $latestMurojaah->surat }} (Juz {{ $latestMurojaah->juz }}, Ayat {{ $latestMurojaah->ayat }})
                                                </p>
                                                <p class="text-xs text-gray-500 mt-0.5">Nilai: <span class="font-semibold text-gray-700">{{ $latestMurojaah->nilai }}</span> | Tanggal: {{ $latestMurojaah->tanggal ? \Carbon\Carbon::parse($latestMurojaah->tanggal)->format('d/m/Y') : '-' }}</p>
                                            @else
                                                <p class="text-xs text-gray-400 mt-1">Belum ada catatan murojaah.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800 text-sm">
                            Belum ada data santri yang terhubung dengan akun Wali Murid Anda. Silakan hubungi bagian administrasi pesantren.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
