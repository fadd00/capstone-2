<x-app-layout title="Detail Setoran Hafalan Baru">
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Setoran Hafalan Baru') }}
            </h1>
            <a href="{{ route('ustadz.setoran-hafalan.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm border border-gray-200 sm:rounded-lg p-6 space-y-4">
                <div class="border-b pb-4">
                    <h2 class="text-lg font-bold text-gray-900">{{ $setoran->santri->nama ?? '-' }}</h2>
                    <p class="text-xs text-gray-600">NIS: {{ $setoran->santri->nis ?? '-' }} | Tingkatan: {{ strtoupper($setoran->santri->tingkatan ?? '-') }}</p>
                </div>

                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 text-xs">Tanggal Setoran</dt>
                        <dd class="font-medium text-gray-900">{{ $setoran->tanggal ? \Carbon\Carbon::parse($setoran->tanggal)->format('d F Y') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Ustadz Pembina</dt>
                        <dd class="font-medium text-gray-900">{{ $setoran->ustadz->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Surat &amp; Juz</dt>
                        <dd class="font-semibold text-emerald-800">{{ $setoran->surat }} (Juz {{ $setoran->juz }})</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Ayat</dt>
                        <dd class="font-medium text-gray-900">{{ $setoran->ayat }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Nilai</dt>
                        <dd class="font-bold text-lg text-emerald-700">{{ $setoran->nilai ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Status</dt>
                        <dd>
                            <span class="px-2 py-1 inline-block rounded-full text-xs font-semibold {{ $setoran->status_selesai ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $setoran->status_selesai ? 'Selesai Target' : 'Belum Selesai' }}
                            </span>
                        </dd>
                    </div>
                </dl>

                <section class="border-t pt-4">
                    <h3 class="text-gray-500 text-xs mb-1">Catatan Ustadz</h3>
                    <p class="text-sm text-gray-800 italic bg-gray-50 p-4 rounded-md border border-gray-100">
                        {{ $setoran->catatan ?: 'Tidak ada catatan.' }}
                    </p>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
