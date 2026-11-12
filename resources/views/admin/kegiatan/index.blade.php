<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kegiatan Pondok') }}
            </h2>
            <a href="{{ route('admin.kegiatan.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
                + Tambah Kegiatan
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white p-6 shadow sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Nama Kegiatan</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Tingkatan</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Kelas</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Deskripsi</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($kegiatanList as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $item->nama_kegiatan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap uppercase text-xs">{{ $item->tingkatan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $item->kelas ?? 'Semua' }}</td>
                                    <td class="px-6 py-4 text-gray-600 max-w-xs truncate">{{ $item->deskripsi ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                        <a href="{{ route('admin.kegiatan.show', $item->id) }}" class="text-blue-600 hover:text-blue-900">Detail</a>
                                        <a href="{{ route('admin.kegiatan.edit', $item->id) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                        <form action="{{ route('admin.kegiatan.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus kegiatan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
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
