<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Penugasan Kegiatan Ustadz') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white p-6 shadow sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Ustadz Pengampu</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Kegiatan Ditugaskan</th>
                            <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($ustadzList as $ustadz)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 align-top">
                                    {{ $ustadz->name }}
                                    <p class="text-xs text-gray-500">{{ $ustadz->email }}</p>
                                </td>
                                <td class="px-6 py-4 text-gray-600 align-top">
                                    <form id="form-kegiatan-{{ $ustadz->id }}" method="POST" action="{{ route('admin.penugasan-kegiatan.update', $ustadz->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach($kegiatanList as $kegiatan)
                                                <label class="inline-flex items-center text-sm">
                                                    <input type="checkbox" name="kegiatan_ids[]" value="{{ $kegiatan->id }}"
                                                        {{ $ustadz->kegiatanPondok->contains($kegiatan->id) ? 'checked' : '' }}
                                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                    <span class="ml-2 text-gray-700">{{ $kegiatan->nama_kegiatan }} ({{ strtoupper($kegiatan->tingkatan) }})</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right align-top">
                                    <button type="submit" form="form-kegiatan-{{ $ustadz->id }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-xs">
                                        Simpan Penugasan
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">Belum ada data ustadz.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
