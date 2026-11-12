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
        $user = auth()->user();
        $mySantriIds = Santri::where('wali_murid_id', $user->id)->pluck('id')->toArray();

        if ($request->filled('santri_id')) {
            $santriId = (int) $request->santri_id;
            if (! in_array($santriId, $mySantriIds)) {
                abort(403, 'Akses data santri ditolak.');
            }
            $targetSantriIds = [$santriId];
        } else {
            $targetSantriIds = $mySantriIds;
        }

        // Default week: Monday to Sunday
        if ($request->filled('tanggal_mulai')) {
            $startDate = Carbon::parse($request->tanggal_mulai)->startOfDay();
        } else {
            $startDate = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        }

        if ($request->filled('tanggal_selesai')) {
            $endDate = Carbon::parse($request->tanggal_selesai)->endOfDay();
        } else {
            $endDate = now()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        }

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

        $children = Santri::where('wali_murid_id', $user->id)->get();

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
        $user = auth()->user();
        $mySantriIds = Santri::where('wali_murid_id', $user->id)->pluck('id')->toArray();

        if ($request->filled('santri_id')) {
            $santriId = (int) $request->santri_id;
            if (! in_array($santriId, $mySantriIds)) {
                abort(403, 'Akses data santri ditolak.');
            }
            $targetSantriIds = [$santriId];
        } else {
            $targetSantriIds = $mySantriIds;
        }

        $query = PresensiSantri::with(['santri', 'presensiKegiatan.kegiatanPondok', 'presensiKegiatan.ustadz'])
            ->whereIn('santri_id', $targetSantriIds);

        if ($request->filled('bulan')) {
            $query->whereHas('presensiKegiatan', function ($q) use ($request) {
                $q->where('tanggal', 'like', $request->bulan.'%');
            });
        }

        $presensiList = $query->latest('id')->get();
        $children = Santri::where('wali_murid_id', $user->id)->get();

        return view('wali.riwayat-kegiatan', compact('presensiList', 'children'));
    }
}
