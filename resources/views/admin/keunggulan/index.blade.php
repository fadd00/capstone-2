<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Keunggulan Pesantren') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Add Keunggulan Form -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Tambah Poin Keunggulan</h3>
                <form action="{{ route('admin.keunggulan.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="judul" class="block text-sm font-medium text-gray-700">Judul Keunggulan</label>
                            <input type="text" name="judul" id="judul" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label for="order" class="block text-sm font-medium text-gray-700">Urutan (Angka)</label>
                            <input type="number" name="order" id="order" value="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>
                    <div>
                        <label for="deskripsi_singkat" class="block text-sm font-medium text-gray-700">Deskripsi Singkat</label>
                        <input type="text" name="deskripsi_singkat" id="deskripsi_singkat" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label for="deskripsi_detail" class="block text-sm font-medium text-gray-700">Deskripsi Lengkap / Detail</label>
                        <textarea name="deskripsi_detail" id="deskripsi_detail" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm text-sm">
                            Simpan Keunggulan
                        </button>
                    </div>
                </form>
            </div>

            <!-- List Keunggulan -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Daftar Keunggulan</h3>
                <div class="divide-y divide-gray-200">
                    @forelse ($keunggulans as $item)
                        <div class="py-4 flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-gray-900">{{ $item->judul }} <span class="text-xs text-gray-500 font-normal">(Urutan: {{ $item->order }})</span></h4>
                                <p class="text-sm text-gray-600 mt-1">{{ $item->deskripsi_singkat }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $item->deskripsi_detail }}</p>
                            </div>
                            <form action="{{ route('admin.keunggulan.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus keunggulan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Belum ada poin keunggulan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
