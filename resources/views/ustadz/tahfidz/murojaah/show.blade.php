<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Murojaah Hafalan') }}
            </h2>
            <a href="{{ route('ustadz.murojaah.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
                <div class="border-b pb-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ $murojaah->santri->nama ?? '-' }}</h3>
                    <p class="text-xs text-gray-500">NIS: {{ $murojaah->santri->nis ?? '-' }} | Tingkatan: {{ strtoupper($murojaah->santri->tingkatan ?? '-') }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 block text-xs">Tanggal Murojaah:</span>
                        <span class="font-medium text-gray-900">{{ $murojaah->tanggal ? \Carbon\Carbon::parse($murojaah->tanggal)->format('d F Y') : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Ustadz Pembina:</span>
                        <span class="font-medium text-gray-900">{{ $murojaah->ustadz->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Surat & Juz:</span>
                        <span class="font-semibold text-green-700">{{ $murojaah->surat }} (Juz {{ $murojaah->juz }})</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Ayat:</span>
                        <span class="font-medium text-gray-900">{{ $murojaah->ayat }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Nilai:</span>
                        <span class="font-bold text-lg text-green-600">{{ $murojaah->nilai ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Status:</span>
                        <span class="px-2 py-1 inline-block rounded-full text-xs font-semibold {{ $murojaah->status_selesai ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $murojaah->status_selesai ? 'Selesai Target' : 'Belum Selesai' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
