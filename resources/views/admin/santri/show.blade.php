<x-app-layout title="Detail Santri">
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Santri: ') . $santri->nama }}
            </h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.santri.edit', $santri->id) }}" class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 text-sm">Edit</a>
                <a href="{{ route('admin.santri.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm border border-gray-200 sm:rounded-lg space-y-4">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Nama Lengkap</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $santri->nama }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">NIS</dt>
                        <dd class="text-lg font-mono text-gray-900">{{ $santri->nis }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Tingkatan</dt>
                        <dd class="text-base uppercase text-gray-800">{{ $santri->tingkatan }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Kelas</dt>
                        <dd class="text-base text-gray-800">{{ $santri->kelas ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Wali Murid</dt>
                        <dd class="text-base text-gray-800">{{ $santri->waliMurid->name ?? '-' }} ({{ $santri->waliMurid->email ?? 'Belum ada wali' }})</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Tanggal Lahir</dt>
                        <dd class="text-base text-gray-800">{{ optional($santri->tanggal_lahir)->format('d F Y') ?? '-' }}</dd>
                    </div>
                </dl>

                <section class="border-t pt-4">
                    <h2 class="font-medium text-gray-800 mb-2">Ustadz Pembina</h2>
                    @if($santri->ustadzPembina->isNotEmpty())
                        <ul class="list-disc pl-5 text-sm text-gray-600">
                            @foreach($santri->ustadzPembina as $ustadz)
                                <li>{{ $ustadz->name }} ({{ $ustadz->email }})</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-600">Belum ada ustadz pembina yang ditugaskan.</p>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
