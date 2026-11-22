<x-app-layout title="Profil Wali Murid">
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Profil Wali Murid: ') . $wali->name }}
            </h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.wali-murid.edit', $wali->id) }}" class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 text-sm">Edit</a>
                <a href="{{ route('admin.wali-murid.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm border border-gray-200 sm:rounded-lg space-y-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Nama Lengkap</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $wali->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Alamat Email</dt>
                        <dd class="text-lg text-gray-800">{{ $wali->email }}</dd>
                    </div>
                </dl>

                <section class="border-t pt-4">
                    <h2 class="font-medium text-gray-800 mb-2">Daftar Anak / Santri Terhubung</h2>
                    @if($wali->santri->isNotEmpty())
                        <ul class="divide-y divide-gray-100">
                            @foreach($wali->santri as $child)
                                <li class="py-3 flex justify-between items-center">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $child->nama }}</p>
                                        <p class="text-xs text-gray-600">NIS: {{ $child->nis }} | Tingkatan: {{ strtoupper($child->tingkatan) }} | Kelas: {{ $child->kelas ?? '-' }}</p>
                                    </div>
                                    <a href="{{ route('admin.santri.show', $child->id) }}" class="text-xs text-emerald-700 hover:text-emerald-900 font-medium">Lihat Detail Santri</a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-600">Belum ada anak/santri yang terhubung.</p>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
