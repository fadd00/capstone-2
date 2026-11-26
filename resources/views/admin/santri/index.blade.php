<x-app-layout title="Data Santri">
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Data Santri') }}
            </h1>
            <a href="{{ route('admin.santri.create') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-md hover:bg-emerald-800 text-sm font-medium">
                + Tambah Santri
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
                <search class="block mb-4">
                    <form method="GET" action="{{ route('admin.santri.index') }}" class="flex flex-wrap items-center gap-2">
                        <label for="search-santri" class="sr-only">Cari nama atau NIS santri</label>
                        <input id="search-santri" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIS..." autocorrect="off" spellcheck="false" enterkeyhint="search" class="rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 w-full max-w-sm text-sm">
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">Cari</button>
                        @if(request('search'))
                            <a href="{{ route('admin.santri.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm hover:bg-gray-300">Reset</a>
                        @endif
                    </form>
                </search>

                <div class="responsive-table-wrapper" tabindex="0" role="region" aria-labelledby="santri-table-caption">
                    <table class="responsive-table min-w-full divide-y divide-gray-200 text-sm">
                        <caption id="santri-table-caption" class="sr-only">Daftar master data santri</caption>
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">NIS</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Nama</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Tingkatan</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Kelas</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Wali Murid</th>
                                <th scope="col" class="px-6 py-3 text-right font-medium text-gray-600 uppercase text-xs">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($santriList as $item)
                                <tr>
                                    <td data-label="NIS" class="px-6 py-4 whitespace-nowrap font-mono text-gray-700">{{ $item->nis }}</td>
                                    <td data-label="Nama" class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $item->nama }}</td>
                                    <td data-label="Tingkatan" class="px-6 py-4 whitespace-nowrap uppercase text-xs">{{ $item->tingkatan }}</td>
                                    <td data-label="Kelas" class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $item->kelas ?? '-' }}</td>
                                    <td data-label="Wali Murid" class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $item->waliMurid->name ?? '-' }}</td>
                                    <td data-label="Aksi" class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                        <a href="{{ route('admin.santri.show', $item->id) }}" class="text-blue-700 hover:text-blue-900">Detail <span class="sr-only">{{ $item->nama }}</span></a>
                                        <a href="{{ route('admin.santri.edit', $item->id) }}" class="text-amber-700 hover:text-amber-900">Edit <span class="sr-only">{{ $item->nama }}</span></a>
                                        <form action="{{ route('admin.santri.destroy', $item->id) }}" method="POST" class="inline" data-confirm="Hapus data santri {{ $item->nama }}?" data-confirm-title="Hapus Santri">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-700 hover:text-red-900">Hapus <span class="sr-only">{{ $item->nama }}</span></button>
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
