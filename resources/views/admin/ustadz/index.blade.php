<x-app-layout title="Data Ustadz">
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Data Ustadz') }}
            </h1>
            <a href="{{ route('admin.ustadz.create') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-md hover:bg-emerald-800 text-sm font-medium">
                + Tambah Ustadz
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
                <div class="responsive-table-wrapper" tabindex="0" role="region" aria-labelledby="ustadz-table-caption">
                    <table class="responsive-table min-w-full divide-y divide-gray-200 text-sm">
                        <caption id="ustadz-table-caption" class="sr-only">Daftar akun ustadz pengajar</caption>
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Nama Ustadz</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Email</th>
                                <th scope="col" class="px-6 py-3 text-center font-medium text-gray-600 uppercase text-xs">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($ustadzList as $item)
                                <tr>
                                    <td data-label="Nama Ustadz" class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $item->name }}</td>
                                    <td data-label="Email" class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $item->email }}</td>
                                    <td data-label="Aksi" class="px-6 py-4 whitespace-nowrap text-center justify-center space-x-2">
                                        <a href="{{ route('admin.ustadz.show', $item->id) }}" class="text-blue-700 hover:text-blue-900">Detail <span class="sr-only">{{ $item->name }}</span></a>
                                        <a href="{{ route('admin.ustadz.edit', $item->id) }}" class="text-amber-700 hover:text-amber-900">Edit <span class="sr-only">{{ $item->name }}</span></a>
                                        <form action="{{ route('admin.ustadz.destroy', $item->id) }}" method="POST" class="inline" data-confirm="Hapus akun ustadz {{ $item->name }}?" data-confirm-title="Hapus Ustadz">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-700 hover:text-red-900">Hapus <span class="sr-only">{{ $item->name }}</span></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada data ustadz.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
