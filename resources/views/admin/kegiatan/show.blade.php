<x-app-layout title="Detail Kegiatan Pondok">
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Kegiatan: ') . $kegiatan->nama_kegiatan }}
            </h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.kegiatan.edit', $kegiatan->id) }}" class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 text-sm">Edit</a>
                <a href="{{ route('admin.kegiatan.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm border border-gray-200 sm:rounded-lg space-y-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Nama Kegiatan</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $kegiatan->nama_kegiatan }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Target Tingkatan</dt>
                        <dd class="text-base uppercase text-gray-800">{{ $kegiatan->tingkatan }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Target Kelas</dt>
                        <dd class="text-base text-gray-800">{{ $kegiatan->kelas ?? 'Semua Kelas' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Deskripsi</dt>
                        <dd class="text-sm text-gray-700">{{ $kegiatan->deskripsi ?? '-' }}</dd>
                    </div>
                </dl>

                <section class="border-t pt-4">
                    <h2 class="font-medium text-gray-800 mb-2">Ustadz Pengampu / Ditugaskan</h2>
                    @if($kegiatan->ustadz->isNotEmpty())
                        <ul class="list-disc pl-5 text-sm text-gray-600">
                            @foreach($kegiatan->ustadz as $ustadz)
                                <li>{{ $ustadz->name }} ({{ $ustadz->email }})</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-600">Belum ada ustadz yang ditugaskan ke kegiatan ini.</p>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
