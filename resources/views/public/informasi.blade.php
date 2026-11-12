@extends('layouts.public')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-gray-200 pb-5">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Pengumuman & Informasi Publik</h1>
        <p class="mt-2 text-sm text-gray-500">Jadwal seleksi, tata tertib, dan informasi resmi seputar PPDB dan kepesantrenan.</p>
    </div>

    <div class="space-y-6">
        @forelse ($informasis as $info)
            <article class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                <header class="mb-3">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ $info->judul }}
                    </h2>
                    @if ($info->created_at)
                        <time class="text-xs text-gray-400" datetime="{{ $info->created_at->toIso8601String() }}">
                            {{ $info->created_at->format('d M Y') }}
                        </time>
                    @endif
                </header>
                <div class="text-gray-700 text-sm leading-relaxed prose max-w-none">
                    {{ $info->isi }}
                </div>
            </article>
        @empty
            <div class="bg-white rounded-xl border border-gray-100 p-12 text-center">
                <p class="text-gray-500 text-sm">Belum ada pengumuman resmi yang diterbitkan.</p>
            </div>
        @endforelse
    </div>

    @if ($informasis->hasPages())
        <div class="pt-6">
            {{ $informasis->links() }}
        </div>
    @endif
</div>
@endsection
