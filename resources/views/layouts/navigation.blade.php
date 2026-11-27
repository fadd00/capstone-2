@php
    $user = Auth::user();
    $menuGroups = [];

    if ($user?->hasRole('admin')) {
        $menuGroups = [
            ['title' => null, 'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => request()->routeIs('admin.dashboard')],
            ]],
            ['title' => 'Akademik & Presensi', 'items' => [
                ['label' => 'Data Santri', 'route' => 'admin.santri.index', 'active' => request()->routeIs('admin.santri.*')],
                ['label' => 'Data Ustadz', 'route' => 'admin.ustadz.index', 'active' => request()->routeIs('admin.ustadz.*')],
                ['label' => 'Data Wali Murid', 'route' => 'admin.wali-murid.index', 'active' => request()->routeIs('admin.wali-murid.*')],
                ['label' => 'Kegiatan Pondok', 'route' => 'admin.kegiatan.index', 'active' => request()->routeIs('admin.kegiatan.*')],
                ['label' => 'Penugasan Kegiatan', 'route' => 'admin.penugasan-kegiatan.index', 'active' => request()->routeIs('admin.penugasan-kegiatan.*')],
                ['label' => 'Santri Binaan', 'route' => 'admin.santri-binaan.index', 'active' => request()->routeIs('admin.santri-binaan.*')],
            ]],
            ['title' => 'Laporan', 'items' => [
                ['label' => 'Presensi Ustadz', 'route' => 'admin.laporan.presensi-ustadz', 'active' => request()->routeIs('admin.laporan.presensi-ustadz')],
                ['label' => 'Presensi Santri', 'route' => 'admin.laporan.presensi-santri', 'active' => request()->routeIs('admin.laporan.presensi-santri')],
                ['label' => 'Laporan Ziyadah', 'route' => 'admin.laporan.hafalan', 'active' => request()->routeIs('admin.laporan.hafalan')],
                ['label' => 'Laporan Murojaah', 'route' => 'admin.laporan.murojaah', 'active' => request()->routeIs('admin.laporan.murojaah')],
            ]],
            ['title' => 'Pengaturan', 'items' => [
                ['label' => 'Akun Admin', 'route' => 'admin.akun.index', 'active' => request()->routeIs('admin.akun.*')],
            ]],
        ];
    } elseif ($user?->hasRole('ustadz')) {
        $menuGroups = [
            ['title' => null, 'items' => [
                ['label' => 'Dashboard', 'route' => 'ustadz.dashboard', 'active' => request()->routeIs('ustadz.dashboard')],
            ]],
            ['title' => 'Presensi', 'items' => [
                ['label' => 'Check-in GPS', 'route' => 'ustadz.presensi-kegiatan.create', 'active' => request()->routeIs('ustadz.presensi-kegiatan.create')],
                ['label' => 'Riwayat & Presensi Santri', 'route' => 'ustadz.presensi-kegiatan.index', 'active' => request()->routeIs('ustadz.presensi-kegiatan.index', 'ustadz.presensi-kegiatan.show', 'ustadz.presensi-santri.*')],
            ]],
            ['title' => 'Tahfidz', 'items' => [
                ['label' => 'Setoran Hafalan (Ziyadah)', 'route' => 'ustadz.setoran-hafalan.index', 'active' => request()->routeIs('ustadz.setoran-hafalan.*')],
                ['label' => 'Murojaah Hafalan', 'route' => 'ustadz.murojaah.index', 'active' => request()->routeIs('ustadz.murojaah.*')],
            ]],
        ];
    } elseif ($user?->hasRole('wali_murid')) {
        $menuGroups = [
            ['title' => null, 'items' => [
                ['label' => 'Dashboard', 'route' => 'wali-murid.dashboard', 'active' => request()->routeIs('wali-murid.dashboard')],
            ]],
            ['title' => 'Monitoring Santri', 'items' => [
                ['label' => 'Laporan Mingguan Tahfidz', 'route' => 'wali-murid.laporan-mingguan.index', 'active' => request()->routeIs('wali-murid.laporan-mingguan.*')],
                ['label' => 'Riwayat Kehadiran', 'route' => 'wali-murid.riwayat-kegiatan.index', 'active' => request()->routeIs('wali-murid.riwayat-kegiatan.*')],
            ]],
        ];
    } else {
        $menuGroups = [
            ['title' => null, 'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => request()->routeIs('dashboard')],
            ]],
        ];
    }
@endphp

<!-- Mobile Drawer Backdrop -->
<div id="app-sidebar-backdrop"
     aria-hidden="true"
     class="hidden fixed inset-0 bg-slate-950/40 backdrop-blur-sm z-30 lg:hidden"></div>

<!-- Sidebar / Responsive Drawer -->
<aside id="app-sidebar"
       aria-label="Menu utama"
       class="app-sidebar fixed inset-y-0 left-0 z-40 w-72 bg-blue-950/90 border-r border-blue-100/20 backdrop-blur-lg flex flex-col -translate-x-full transition-transform duration-200 lg:translate-x-0 lg:static lg:inset-auto">
    <div class="h-20 px-5 border-b border-slate-100 flex items-center justify-between shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0">
            <img src="{{ asset('images/logo-ibnu-juraimi.png') }}" alt="" width="300" height="247" class="logo-on-light h-11 w-auto shrink-0">
            <span class="min-w-0">
                <span class="block truncate text-sm font-bold text-blue-950">PPTQ Ibnu Juraimi</span>
                <span class="block truncate text-[11px] font-medium text-slate-400">Sistem Informasi Pesantren</span>
            </span>
        </a>
        <button id="sidebar-close-btn"
                type="button"
                aria-label="Tutup menu"
                class="lg:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100">
            <svg class="h-5 w-5" aria-hidden="true" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav aria-label="Utama" class="flex-1 overflow-y-auto px-4 py-6 space-y-7 text-sm">
        @foreach ($menuGroups as $group)
            <div>
                @if ($group['title'])
                    <p class="px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-[0.16em]">
                        {{ $group['title'] }}
                    </p>
                @endif
                <ul class="space-y-1">
                    @foreach ($group['items'] as $item)
                        <li>
                            <a href="{{ route($item['route']) }}"
                               @if ($item['active']) aria-current="page" @endif
                               class="app-nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ $item['active'] ? 'bg-blue-50 text-blue-900 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-900' }}">
                                <span aria-hidden="true" class="h-1.5 w-1.5 rounded-full {{ $item['active'] ? 'bg-blue-600' : 'bg-slate-300' }}"></span>
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    <div class="mt-auto pt-3 border-t border-white/15 px-4 pb-4 shrink-0">
        <div class="flex items-center justify-between gap-2.5">
            <a href="{{ route('profile.edit') }}"
               class="app-nav-link flex min-w-0 flex-1 items-center gap-2.5 rounded-xl px-2 py-2 transition">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/15 text-xs font-bold text-white">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(Auth::user()->name, 0, 1)) }}
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-xs font-medium text-white">{{ Auth::user()->name }}</span>
                    <span class="block truncate text-[10px] text-blue-200">{{ __('Profil') }}</span>
                </span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit"
                        title="Keluar"
                        aria-label="Keluar"
                        class="app-sidebar-logout shrink-0 rounded-lg p-2 text-blue-100 transition hover:bg-red-500/20 hover:text-white">
                    <svg class="h-5 w-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 8.25V5.625A2.625 2.625 0 0 0 13.125 3h-6A2.625 2.625 0 0 0 4.5 5.625v12.75A2.625 2.625 0 0 0 7.125 21h6a2.625 2.625 0 0 0 2.625-2.625V15.75m-4.5-3.75h9m0 0-3.75-3.75M20.25 12l-3.75 3.75" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
