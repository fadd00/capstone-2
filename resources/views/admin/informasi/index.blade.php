<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Informasi & Pengumuman Publik') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Add Informasi Form -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Buat Pengumuman Baru</h3>
                <form action="{{ route('admin.informasi.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="judul" class="block text-sm font-medium text-gray-700">Judul Pengumuman</label>
                        <input type="text" name="judul" id="judul" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label for="isi" class="block text-sm font-medium text-gray-700">Isi Pengumuman</label>
                        <textarea name="isi" id="isi" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm text-sm">
                            Publikasikan Informasi
                        </button>
                    </div>
                </form>
            </div>

            <!-- List Informasi -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Daftar Pengumuman</h3>
                <div class="divide-y divide-gray-200">
                    @forelse ($informasis as $item)
                        <div class="py-4 flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-gray-900">{{ $item->judul }}</h4>
                                <p class="text-xs text-gray-500 mb-2">{{ $item->created_at?->format('d M Y H:i') ?? '-' }}</p>
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $item->isi }}</p>
                            </div>
                            <form action="{{ route('admin.informasi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus informasi ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Belum ada informasi publik.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
