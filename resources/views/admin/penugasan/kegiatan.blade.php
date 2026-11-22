<x-app-layout title="Penugasan Kegiatan Ustadz">
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Penugasan Kegiatan Ustadz') }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div role="status" aria-live="polite" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            <div class="bg-white p-6 shadow-sm border border-gray-200 sm:rounded-lg">
                <div class="responsive-table-wrapper" tabindex="0" role="region" aria-labelledby="penugasan-kegiatan-caption">
                    <table class="responsive-table min-w-full divide-y divide-gray-200 text-sm">
                        <caption id="penugasan-kegiatan-caption" class="sr-only">Penugasan kegiatan pondok per ustadz</caption>
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Ustadz Pengampu</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Kegiatan Ditugaskan</th>
                                <th scope="col" class="px-6 py-3 text-right font-medium text-gray-600 uppercase text-xs">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($ustadzList as $ustadz)
                                <tr>
                                    <td data-label="Ustadz Pengampu" class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 align-top">
                                        {{ $ustadz->name }}
                                        <p class="text-xs text-gray-500">{{ $ustadz->email }}</p>
                                    </td>
                                    <td data-label="Kegiatan Ditugaskan" class="px-6 py-4 text-gray-600 align-top">
                                        <form id="form-kegiatan-{{ $ustadz->id }}" method="POST" action="{{ route('admin.penugasan-kegiatan.update', $ustadz->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <fieldset>
                                                <legend class="sr-only">Kegiatan untuk {{ $ustadz->name }}</legend>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-start">
                                                    @foreach($kegiatanList as $kegiatan)
                                                        <label for="keg-{{ $ustadz->id }}-{{ $kegiatan->id }}" class="inline-flex items-center text-sm">
                                                            <input id="keg-{{ $ustadz->id }}-{{ $kegiatan->id }}"
                                                                   type="checkbox"
                                                                   name="kegiatan_ids[]"
                                                                   value="{{ $kegiatan->id }}"
                                                                   {{ $ustadz->kegiatanPondok->contains($kegiatan->id) ? 'checked' : '' }}
                                                                   class="rounded border-gray-300 text-emerald-700 shadow-sm focus:ring-emerald-600">
                                                            <span class="ms-2 text-gray-700">{{ $kegiatan->nama_kegiatan }} ({{ strtoupper($kegiatan->tingkatan) }})</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </fieldset>
                                        </form>
                                    </td>
                                    <td data-label="Aksi" class="px-6 py-4 whitespace-nowrap text-right align-top">
                                        <button type="submit" form="form-kegiatan-{{ $ustadz->id }}" class="px-4 py-2 bg-emerald-700 text-white rounded-md hover:bg-emerald-800 text-xs font-medium">
                                            Simpan Penugasan <span class="sr-only">{{ $ustadz->name }}</span>
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
    </div>
</x-app-layout>
