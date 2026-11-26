<x-app-layout title="Kegiatan Pondok">
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kegiatan Pondok') }}
            </h1>
            <a href="{{ route('admin.kegiatan.create') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-md hover:bg-emerald-800 text-sm font-medium">
                + Tambah Kegiatan
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div role="status" aria-live="polite" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div role="alert" class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            <div class="bg-white p-6 shadow-sm border border-gray-200 sm:rounded-lg">
                <div class="responsive-table-wrapper" tabindex="0" role="region" aria-labelledby="kegiatan-table-caption">
                    <table class="responsive-table min-w-full divide-y divide-gray-200 text-sm">
                        <caption id="kegiatan-table-caption" class="sr-only">Daftar kegiatan pondok pesantren</caption>
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Nama Kegiatan</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Tingkatan</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Kelas</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Deskripsi</th>
                                <th scope="col" class="px-6 py-3 text-right font-medium text-gray-600 uppercase text-xs">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($kegiatanList as $item)
                                <tr>
                                    <td data-label="Nama Kegiatan" class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $item->nama_kegiatan }}</td>
                                    <td data-label="Tingkatan" class="px-6 py-4 whitespace-nowrap uppercase text-xs">{{ $item->tingkatan }}</td>
                                    <td data-label="Kelas" class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $item->kelas ?? 'Semua' }}</td>
                                    <td data-label="Deskripsi" class="px-6 py-4 text-gray-600 max-w-xs truncate">{{ $item->deskripsi ?? '-' }}</td>
                                    <td data-label="Aksi" class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                        <a href="{{ route('admin.kegiatan.show', $item->id) }}" class="text-blue-700 hover:text-blue-900">Detail <span class="sr-only">{{ $item->nama_kegiatan }}</span></a>
                                        <a href="{{ route('admin.kegiatan.edit', $item->id) }}" class="text-amber-700 hover:text-amber-900">Edit <span class="sr-only">{{ $item->nama_kegiatan }}</span></a>
                                        <form action="{{ route('admin.kegiatan.destroy', $item->id) }}" method="POST" class="inline" data-confirm="Hapus kegiatan {{ $item->nama_kegiatan }}?" data-confirm-title="Hapus Kegiatan">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-700 hover:text-red-900">Hapus <span class="sr-only">{{ $item->nama_kegiatan }}</span></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada kegiatan pondok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
