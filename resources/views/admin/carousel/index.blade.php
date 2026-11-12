<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Banner Carousel Beranda') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Add Banner Form -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Tambah Banner Carousel</h3>
                <form action="{{ route('admin.carousel.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">Gambar Banner</label>
                        <input type="file" name="image" id="image" required class="mt-1 block w-full text-sm">
                        @error('image')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="caption" class="block text-sm font-medium text-gray-700">Caption / Judul Banner</label>
                        <input type="text" name="caption" id="caption" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label for="order" class="block text-sm font-medium text-gray-700">Urutan (Angka)</label>
                        <input type="number" name="order" id="order" value="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div class="md:col-span-3 flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm text-sm">
                            Upload Banner
                        </button>
                    </div>
                </form>
            </div>

            <!-- List Banners -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Daftar Banner Aktif</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @forelse ($carousels as $carousel)
                        <div class="border rounded-lg p-4 space-y-2">
                            <img src="{{ asset('storage/' . $carousel->image) }}" alt="{{ $carousel->caption }}" class="w-full h-40 object-cover rounded">
                            <div class="font-semibold text-sm">{{ $carousel->caption }}</div>
                            <div class="text-xs text-gray-500">Urutan: {{ $carousel->order }}</div>
                            <div class="flex justify-between items-center pt-2">
                                <form action="{{ route('admin.carousel.destroy', $carousel->id) }}" method="POST" onsubmit="return confirm('Hapus banner ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 col-span-3">Belum ada banner carousel.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
