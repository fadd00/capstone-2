<?php

namespace App\Http\Controllers\WaliMurid;

use App\Http\Controllers\Controller;
use App\Models\Murojaah;
use App\Models\PresensiSantri;
use App\Models\Santri;
use App\Models\SetoranHafalan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaliMuridReportController extends Controller
{
    public function laporanMingguan(Request $request): View
    {
        [$children, $targetSantriIds] = $this->resolveAllowedSantri($request);

        $startDate = $request->filled('tanggal_mulai')
            ? Carbon::parse($request->tanggal_mulai)->startOfDay()
            : now()->startOfWeek(Carbon::MONDAY)->startOfDay();

        $endDate = $request->filled('tanggal_selesai')
            ? Carbon::parse($request->tanggal_selesai)->endOfDay()
            : now()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $setoranList = SetoranHafalan::with(['santri', 'ustadz'])
            ->whereIn('santri_id', $targetSantriIds)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $murojaahList = Murojaah::with(['santri', 'ustadz'])
            ->whereIn('santri_id', $targetSantriIds)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('wali.laporan-mingguan', compact(
            'setoranList',
            'murojaahList',
            'children',
            'startDate',
            'endDate'
        ));
    }

    public function riwayatKehadiran(Request $request): View
    {
        [$children, $targetSantriIds] = $this->resolveAllowedSantri($request);

        $query = PresensiSantri::with(['santri', 'presensiKegiatan.kegiatanPondok', 'presensiKegiatan.ustadz'])
            ->whereIn('santri_id', $targetSantriIds);

        if ($request->filled('bulan')) {
            $query->whereHas('presensiKegiatan', function ($q) use ($request) {
                $q->where('tanggal', 'like', $request->bulan.'%');
            });
        }

        $presensiList = $query->latest('id')->get();

        return view('wali.riwayat-kegiatan', compact('presensiList', 'children'));
    }

    private function resolveAllowedSantri(Request $request): array
    {
        $request->validate([
            'santri_id' => ['nullable', 'integer'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date'],
            'bulan' => ['nullable', 'date_format:Y-m'],
        ]);

        $children = Santri::where('wali_murid_id', auth()->id())->get();
        $mySantriIds = $children->pluck('id')->all();

        if ($request->filled('santri_id')) {
            $santriId = (int) $request->santri_id;
            if (! in_array($santriId, $mySantriIds, true)) {
                abort(403, 'Akses data santri ditolak.');
            }

            return [$children, [$santriId]];
        }

        return [$children, $mySantriIds];
    }
}
