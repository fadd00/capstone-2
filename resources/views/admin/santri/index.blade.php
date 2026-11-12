<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Data Santri') }}
            </h2>
            <a href="{{ route('admin.santri.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
                + Tambah Santri
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white p-6 shadow sm:rounded-lg">
                <form method="GET" action="{{ route('admin.santri.index') }}" class="mb-4 flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIS..." class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full max-w-sm text-sm">
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded text-sm hover:bg-gray-700">Cari</button>
                    @if(request('search'))
                        <a href="{{ route('admin.santri.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">Reset</a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">NIS</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Tingkatan</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Kelas</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Wali Murid</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($santriList as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-gray-700">{{ $item->nis }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $item->nama }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap uppercase text-xs">{{ $item->tingkatan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $item->kelas ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $item->waliMurid->name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                        <a href="{{ route('admin.santri.show', $item->id) }}" class="text-blue-600 hover:text-blue-900">Detail</a>
                                        <a href="{{ route('admin.santri.edit', $item->id) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                        <form action="{{ route('admin.santri.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus data santri ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data santri.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
