<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Santri: ') . $santri->nama }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('admin.santri.edit', $santri->id) }}" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-sm">Edit</a>
                <a href="{{ route('admin.santri.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow sm:rounded-lg space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Nama Lengkap</span>
                        <p class="text-lg font-semibold text-gray-900">{{ $santri->nama }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">NIS</span>
                        <p class="text-lg font-mono text-gray-900">{{ $santri->nis }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Tingkatan</span>
                        <p class="text-base uppercase text-gray-800">{{ $santri->tingkatan }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Kelas</span>
                        <p class="text-base text-gray-800">{{ $santri->kelas ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Wali Murid</span>
                        <p class="text-base text-gray-800">{{ $santri->waliMurid->name ?? '-' }} ({{ $santri->waliMurid->email ?? 'Belum ada wali' }})</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Tanggal Lahir</span>
                        <p class="text-base text-gray-800">{{ optional($santri->tanggal_lahir)->format('d F Y') ?? '-' }}</p>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <h4 class="font-medium text-gray-800 mb-2">Ustadz Pembina</h4>
                    @if($santri->ustadzPembina->isNotEmpty())
                        <ul class="list-disc pl-5 text-sm text-gray-600">
                            @foreach($santri->ustadzPembina as $ustadz)
                                <li>{{ $ustadz->name }} ({{ $ustadz->email }})</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500">Belum ada ustadz pembina yang ditugaskan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
