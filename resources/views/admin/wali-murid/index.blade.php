<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Data Wali Murid') }}
            </h2>
            <a href="{{ route('admin.wali-murid.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
                + Tambah Wali Murid
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
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Nama Wali Murid</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Jumlah Anak</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($waliMuridList as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $item->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $item->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $item->santri->count() }} Santri</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                        <a href="{{ route('admin.wali-murid.show', $item->id) }}" class="text-blue-600 hover:text-blue-900">Detail</a>
                                        <a href="{{ route('admin.wali-murid.edit', $item->id) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                        <form action="{{ route('admin.wali-murid.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus akun wali murid ini? Santri yang terhubung akan diset NULL.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
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
