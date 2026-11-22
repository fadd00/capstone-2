<x-app-layout title="Data Wali Murid">
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Data Wali Murid') }}
            </h1>
            <a href="{{ route('admin.wali-murid.create') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-md hover:bg-emerald-800 text-sm font-medium">
                + Tambah Wali Murid
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div role="status" aria-live="polite" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            <div class="bg-white p-6 shadow-sm border border-gray-200 sm:rounded-lg">
                <div class="responsive-table-wrapper" tabindex="0" role="region" aria-labelledby="wali-table-caption">
                    <table class="responsive-table min-w-full divide-y divide-gray-200 text-sm">
                        <caption id="wali-table-caption" class="sr-only">Daftar akun wali murid</caption>
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Nama Wali Murid</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Email</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Jumlah Anak</th>
                                <th scope="col" class="px-6 py-3 text-right font-medium text-gray-600 uppercase text-xs">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($waliMuridList as $item)
                                <tr>
                                    <td data-label="Nama Wali Murid" class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $item->name }}</td>
                                    <td data-label="Email" class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $item->email }}</td>
                                    <td data-label="Jumlah Anak" class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $item->santri->count() }} Santri</td>
                                    <td data-label="Aksi" class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                        <a href="{{ route('admin.wali-murid.show', $item->id) }}" class="text-blue-700 hover:text-blue-900">Detail <span class="sr-only">{{ $item->name }}</span></a>
                                        <a href="{{ route('admin.wali-murid.edit', $item->id) }}" class="text-amber-700 hover:text-amber-900">Edit <span class="sr-only">{{ $item->name }}</span></a>
                                        <form action="{{ route('admin.wali-murid.destroy', $item->id) }}" method="POST" class="inline" data-confirm="Hapus akun wali murid {{ $item->name }}? Relasi santri yang terhubung akan dilepas." data-confirm-title="Hapus Wali Murid">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-700 hover:text-red-900">Hapus <span class="sr-only">{{ $item->name }}</span></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data wali murid.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
