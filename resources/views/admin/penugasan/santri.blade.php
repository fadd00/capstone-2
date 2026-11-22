<x-app-layout title="Penugasan Santri Binaan">
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Penugasan Santri Binaan') }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div role="status" aria-live="polite" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            <div class="bg-white p-6 shadow-sm border border-gray-200 sm:rounded-lg">
                <div class="responsive-table-wrapper" tabindex="0" role="region" aria-labelledby="penugasan-santri-caption">
                    <table class="responsive-table min-w-full divide-y divide-gray-200 text-sm">
                        <caption id="penugasan-santri-caption" class="sr-only">Penugasan santri binaan per ustadz pembina</caption>
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Ustadz Pembina</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Santri Binaan</th>
                                <th scope="col" class="px-6 py-3 text-right font-medium text-gray-600 uppercase text-xs">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($ustadzList as $ustadz)
                                <tr>
                                    <td data-label="Ustadz Pembina" class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 align-top">
                                        {{ $ustadz->name }}
                                        <p class="text-xs text-gray-500">{{ $ustadz->email }}</p>
                                        <p class="text-xs text-emerald-700 font-semibold mt-1">{{ $ustadz->santriBinaan->count() }} Santri Binaan</p>
                                    </td>
                                    <td data-label="Santri Binaan" class="px-6 py-4 text-gray-600 align-top">
                                        <form id="form-santri-{{ $ustadz->id }}" method="POST" action="{{ route('admin.santri-binaan.update', $ustadz->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <fieldset>
                                                <legend class="sr-only">Santri binaan untuk {{ $ustadz->name }}</legend>
                                                <div class="max-h-60 overflow-y-auto grid grid-cols-1 md:grid-cols-2 gap-2 border p-3 rounded text-start">
                                                    @foreach($santriList as $santri)
                                                        <label for="sb-{{ $ustadz->id }}-{{ $santri->id }}" class="inline-flex items-center text-sm">
                                                            <input id="sb-{{ $ustadz->id }}-{{ $santri->id }}"
                                                                   type="checkbox"
                                                                   name="santri_ids[]"
                                                                   value="{{ $santri->id }}"
                                                                   {{ $ustadz->santriBinaan->contains($santri->id) ? 'checked' : '' }}
                                                                   class="rounded border-gray-300 text-emerald-700 shadow-sm focus:ring-emerald-600">
                                                            <span class="ms-2 text-gray-700">{{ $santri->nama }} <span class="text-xs text-gray-500">({{ $santri->nis }})</span></span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </fieldset>
                                        </form>
                                    </td>
                                    <td data-label="Aksi" class="px-6 py-4 whitespace-nowrap text-right align-top">
                                        <button type="submit" form="form-santri-{{ $ustadz->id }}" class="px-4 py-2 bg-emerald-700 text-white rounded-md hover:bg-emerald-800 text-xs font-medium">
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
