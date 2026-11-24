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
       class="app-sidebar fixed inset-y-0 left-0 z-40 w-72 bg-white border-r border-slate-200 flex flex-col -translate-x-full transition-transform duration-200 lg:translate-x-0 lg:static lg:inset-auto">
    <div class="h-20 px-5 border-b border-slate-100 flex items-center justify-between shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-sm shadow-blue-600/20">
                <svg class="h-5 w-5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19.5A2.5 2.5 0 016.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" />
                </svg>
            </span>
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
</aside>
