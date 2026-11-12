<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Profil Ustadz: ') . $ustadz->name }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('admin.ustadz.edit', $ustadz->id) }}" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-sm">Edit</a>
                <a href="{{ route('admin.ustadz.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow sm:rounded-lg space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Nama Lengkap</span>
                        <p class="text-lg font-semibold text-gray-900">{{ $ustadz->name }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Alamat Email</span>
                        <p class="text-lg text-gray-800">{{ $ustadz->email }}</p>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <h4 class="font-medium text-gray-800 mb-2">Kegiatan yang Ditugaskan</h4>
                    @if($ustadz->kegiatanPondok->isNotEmpty())
                        <ul class="list-disc pl-5 text-sm text-gray-600">
                            @foreach($ustadz->kegiatanPondok as $kegiatan)
                                <li>{{ $kegiatan->nama_kegiatan }} ({{ $kegiatan->tingkatan }})</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500">Belum ada kegiatan yang ditugaskan.</p>
                    @endif
                </div>

                <div class="border-t pt-4">
                    <h4 class="font-medium text-gray-800 mb-2">Santri Binaan</h4>
                    @if($ustadz->santriBinaan->isNotEmpty())
                        <ul class="list-disc pl-5 text-sm text-gray-600">
                            @foreach($ustadz->santriBinaan as $binaan)
                                <li>{{ $binaan->nama }} - NIS: {{ $binaan->nis }} ({{ $binaan->tingkatan }} {{ $binaan->kelas }})</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500">Belum ada santri binaan yang ditugaskan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
