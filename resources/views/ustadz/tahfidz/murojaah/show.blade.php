<x-app-layout title="Detail Murojaah Hafalan">
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Murojaah Hafalan') }}
            </h1>
            <a href="{{ route('ustadz.murojaah.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm border border-gray-200 sm:rounded-lg p-6 space-y-4">
                <div class="border-b pb-4">
                    <h2 class="text-lg font-bold text-gray-900">{{ $murojaah->santri->nama ?? '-' }}</h2>
                    <p class="text-xs text-gray-600">NIS: {{ $murojaah->santri->nis ?? '-' }} | Tingkatan: {{ strtoupper($murojaah->santri->tingkatan ?? '-') }}</p>
                </div>

                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 text-xs">Tanggal Murojaah</dt>
                        <dd class="font-medium text-gray-900">{{ $murojaah->tanggal ? \Carbon\Carbon::parse($murojaah->tanggal)->format('d F Y') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Ustadz Pembina</dt>
                        <dd class="font-medium text-gray-900">{{ $murojaah->ustadz->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Surat &amp; Juz</dt>
                        <dd class="font-semibold text-emerald-800">{{ $murojaah->surat }} (Juz {{ $murojaah->juz }})</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Ayat</dt>
                        <dd class="font-medium text-gray-900">{{ $murojaah->ayat }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Nilai</dt>
                        <dd class="font-bold text-lg text-emerald-700">{{ $murojaah->nilai ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Status</dt>
                        <dd>
                            <span class="px-2 py-1 inline-block rounded-full text-xs font-semibold {{ $murojaah->status_selesai ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $murojaah->status_selesai ? 'Selesai Target' : 'Belum Selesai' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
