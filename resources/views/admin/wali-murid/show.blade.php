<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Profil Wali Murid: ') . $wali->name }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('admin.wali-murid.edit', $wali->id) }}" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-sm">Edit</a>
                <a href="{{ route('admin.wali-murid.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow sm:rounded-lg space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Nama Lengkap</span>
                        <p class="text-lg font-semibold text-gray-900">{{ $wali->name }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Alamat Email</span>
                        <p class="text-lg text-gray-800">{{ $wali->email }}</p>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <h4 class="font-medium text-gray-800 mb-2">Daftar Anak / Santri Terhubung</h4>
                    @if($wali->santri->isNotEmpty())
                        <div class="divide-y divide-gray-100">
                            @foreach($wali->santri as $child)
                                <div class="py-3 flex justify-between items-center">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $child->nama }}</p>
                                        <p class="text-xs text-gray-500">NIS: {{ $child->nis }} | Tingkatan: {{ strtoupper($child->tingkatan) }} | Kelas: {{ $child->kelas ?? '-' }}</p>
                                    </div>
                                    <a href="{{ route('admin.santri.show', $child->id) }}" class="text-xs text-indigo-600 hover:text-indigo-900">Lihat Detail Santri</a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Belum ada anak/santri yang terhubung.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
