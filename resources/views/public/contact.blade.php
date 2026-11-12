@extends('layouts.public')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-gray-200 pb-5">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Kontak Resmi & Lokasi Pondok</h1>
        <p class="mt-2 text-sm text-gray-500">Sekretariat Penerimaan Peserta Didik Baru (PPDB) PPTQ Muhammadiyah Ibnu Juraimi.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-4 bg-gray-50 rounded-xl">
                <h3 class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-2">Alamat Sekretariat</h3>
                <p class="text-sm text-gray-800 leading-relaxed">
                    {{ $kontak?->alamat ?? 'Alamat belum diatur' }}
                </p>
            </div>
            <div class="p-4 bg-gray-50 rounded-xl">
                <h3 class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-2">Nomor Telepon / WhatsApp</h3>
                <p class="text-sm text-gray-800 font-mono">
                    {{ $kontak?->telepon ?? '-' }}
                </p>
            </div>
            <div class="p-4 bg-gray-50 rounded-xl">
                <h3 class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-2">Email Resmi</h3>
                <p class="text-sm text-gray-800">
                    {{ $kontak?->email ?? '-' }}
                </p>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-6">
            <h3 class="text-base font-semibold text-gray-900 mb-2">Jam Layanan Konsultasi PPDB</h3>
            <p class="text-sm text-gray-600">
                Senin - Sabtu: 08.00 - 15.00 WIB (Kecuali Hari Libur Nasional).
            </p>
        </div>
    </div>
</div>
@endsection
