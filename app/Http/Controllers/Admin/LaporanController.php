<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanExport;
use App\Http\Controllers\Controller;
use App\Models\KegiatanPondok;
use App\Models\Murojaah;
use App\Models\PresensiKegiatan;
use App\Models\PresensiSantri;
use App\Models\Santri;
use App\Models\SetoranHafalan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function presensiUstadz(Request $request)
    {
        $presensiList = $this->getPresensiUstadzQuery($request)->get();
        $ustadzList = User::role('ustadz')->orderBy('name')->get();
        $kegiatanList = KegiatanPondok::orderBy('nama_kegiatan')->get();

        return view('admin.laporan.presensi-ustadz', compact('presensiList', 'ustadzList', 'kegiatanList'));
    }

    public function presensiSantri(Request $request)
    {
        $presensiSantriList = $this->getPresensiSantriQuery($request)->get();
        $santriList = Santri::orderBy('nama')->get();
        $kegiatanList = KegiatanPondok::orderBy('nama_kegiatan')->get();

        return view('admin.laporan.presensi-santri', compact('presensiSantriList', 'santriList', 'kegiatanList'));
    }

    public function hafalan(Request $request)
    {
        $hafalanList = $this->getHafalanQuery($request)->get();
        $santriList = Santri::orderBy('nama')->get();
        $ustadzList = User::role('ustadz')->orderBy('name')->get();

        return view('admin.laporan.hafalan', compact('hafalanList', 'santriList', 'ustadzList'));
    }

    public function murojaah(Request $request)
    {
        $murojaahList = $this->getMurojaahQuery($request)->get();
        $santriList = Santri::orderBy('nama')->get();
        $ustadzList = User::role('ustadz')->orderBy('name')->get();

        return view('admin.laporan.murojaah', compact('murojaahList', 'santriList', 'ustadzList'));
    }

    public function export(Request $request)
    {
        $laporan = $request->query('laporan', 'presensi-ustadz');
        $format = $request->query('format', 'pdf');

        $records = $this->getFilteredRecords($laporan, $request);

        if ($format === 'excel') {
            $content = Excel::raw(new LaporanExport($records, $laporan), \Maatwebsite\Excel\Excel::XLSX);

            return response($content, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"laporan-{$laporan}.xlsx\"",
            ]);
        }

        $title = match ($laporan) {
            'presensi-ustadz' => 'Laporan Presensi Ustadz Global',
            'presensi-santri' => 'Laporan Presensi Santri Global',
            'hafalan' => 'Laporan Setoran Hafalan Baru (Ziyadah)',
            'murojaah' => 'Laporan Murojaah Hafalan',
            default => 'Laporan Akademik Pesantren',
        };

        $pdf = Pdf::loadView('admin.laporan.pdf', [
            'laporan' => $laporan,
            'title' => $title,
            'records' => $records,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("laporan-{$laporan}.pdf");
    }

    protected function getPresensiUstadzQuery(Request $request)
    {
        $query = PresensiKegiatan::with(['ustadz', 'kegiatanPondok'])->latest('tanggal')->latest('id');

        if ($request->filled('ustadz_id')) {
            $query->where('ustadz_id', $request->ustadz_id);
        }
        if ($request->filled('kegiatan_pondok_id')) {
            $query->where('kegiatan_pondok_id', $request->kegiatan_pondok_id);
        }
        if ($request->filled('bulan')) {
            $query->where('tanggal', 'like', $request->bulan.'%');
        }

        return $query;
    }

    protected function getPresensiSantriQuery(Request $request)
    {
        $query = PresensiSantri::with(['santri', 'presensiKegiatan.kegiatanPondok', 'presensiKegiatan.ustadz'])->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tingkatan')) {
            $query->whereHas('santri', function ($q) use ($request) {
                $q->where('tingkatan', $request->tingkatan);
            });
        }
        if ($request->filled('santri_id')) {
            $query->where('santri_id', $request->santri_id);
        }
        if ($request->filled('kegiatan_pondok_id')) {
            $query->whereHas('presensiKegiatan', function ($q) use ($request) {
                $q->where('kegiatan_pondok_id', $request->kegiatan_pondok_id);
            });
        }
        if ($request->filled('bulan')) {
            $query->whereHas('presensiKegiatan', function ($q) use ($request) {
                $q->where('tanggal', 'like', $request->bulan.'%');
            });
        }

        return $query;
    }

    protected function getHafalanQuery(Request $request)
    {
        $query = SetoranHafalan::with(['santri', 'ustadz'])->latest('tanggal')->latest('id');

        if ($request->filled('santri_id')) {
            $query->where('santri_id', $request->santri_id);
        }
        if ($request->filled('ustadz_id')) {
            $query->where('ustadz_id', $request->ustadz_id);
        }
        if ($request->filled('tingkatan')) {
            $query->whereHas('santri', function ($q) use ($request) {
                $q->where('tingkatan', $request->tingkatan);
            });
        }
        if ($request->filled('bulan')) {
            $query->where('tanggal', 'like', $request->bulan.'%');
        }

        return $query;
    }

    protected function getMurojaahQuery(Request $request)
    {
        $query = Murojaah::with(['santri', 'ustadz'])->latest('tanggal')->latest('id');

        if ($request->filled('santri_id')) {
            $query->where('santri_id', $request->santri_id);
        }
        if ($request->filled('ustadz_id')) {
            $query->where('ustadz_id', $request->ustadz_id);
        }
        if ($request->filled('tingkatan')) {
            $query->whereHas('santri', function ($q) use ($request) {
                $q->where('tingkatan', $request->tingkatan);
            });
        }
        if ($request->filled('bulan')) {
            $query->where('tanggal', 'like', $request->bulan.'%');
        }

        return $query;
    }

    protected function getFilteredRecords(string $type, Request $request): Collection
    {
        return match ($type) {
            'presensi-ustadz' => $this->getPresensiUstadzQuery($request)->get(),
            'presensi-santri' => $this->getPresensiSantriQuery($request)->get(),
            'hafalan' => $this->getHafalanQuery($request)->get(),
            'murojaah' => $this->getMurojaahQuery($request)->get(),
            default => collect([]),
        };
    }
}
