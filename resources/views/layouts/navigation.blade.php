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
     class="hidden fixed inset-0 bg-gray-900/40 z-30 lg:hidden"></div>

<!-- Sidebar / Responsive Drawer -->
<aside id="app-sidebar"
       aria-label="Menu utama"
       class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 flex flex-col -translate-x-full transition-transform duration-150 lg:translate-x-0 lg:static lg:inset-auto">
    <div class="h-14 px-4 border-b border-gray-200 flex items-center justify-between shrink-0">
        <a href="{{ route('dashboard') }}" class="font-semibold text-sm text-gray-900 truncate">
            PPTQ Ibnu Juraimi
        </a>
        <button id="sidebar-close-btn"
                type="button"
                aria-label="Tutup menu"
                class="lg:hidden p-2 rounded text-gray-600 hover:text-gray-900 hover:bg-gray-100">
            <svg class="h-5 w-5" aria-hidden="true" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav aria-label="Utama" class="flex-1 overflow-y-auto px-3 py-4 space-y-5 text-sm">
        @foreach ($menuGroups as $group)
            <div>
                @if ($group['title'])
                    <p class="px-3 mb-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {{ $group['title'] }}
                    </p>
                @endif
                <ul class="space-y-0.5">
                    @foreach ($group['items'] as $item)
                        <li>
                            <a href="{{ route($item['route']) }}"
                               @if ($item['active']) aria-current="page" @endif
                               class="block px-3 py-2 rounded-md transition {{ $item['active'] ? 'bg-emerald-50 text-emerald-900 font-semibold border-s-2 border-emerald-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>
</aside>
