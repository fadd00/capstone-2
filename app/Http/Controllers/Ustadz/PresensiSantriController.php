<?php

namespace App\Http\Controllers\Ustadz;

use App\Http\Controllers\Controller;
use App\Models\PresensiKegiatan;
use App\Models\PresensiSantri;
use App\Models\Santri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PresensiSantriController extends Controller
{
    public function create(int $session_id): View
    {
        $session = PresensiKegiatan::with('kegiatanPondok')->findOrFail($session_id);

        if ($session->ustadz_id !== Auth::id() && ! Auth::user()->hasRole('admin')) {
            abort(403, 'Akses tidak sah ke sesi presensi ustadz lain.');
        }

        $query = Santri::query();
        if ($session->kegiatanPondok) {
            if ($session->kegiatanPondok->tingkatan && $session->kegiatanPondok->tingkatan !== 'semua') {
                $query->where('tingkatan', $session->kegiatanPondok->tingkatan);
            }
            if ($session->kegiatanPondok->kelas) {
                $query->where('kelas', $session->kegiatanPondok->kelas);
            }
        }
        $santriList = $query->orderBy('nama')->get();

        $existingAttendance = PresensiSantri::where('presensi_kegiatan_id', $session->id)
            ->pluck('status', 'santri_id')
            ->toArray();

        return view('ustadz.presensi.santri', compact('session', 'santriList', 'existingAttendance'));
    }

    public function store(Request $request, int $session_id): RedirectResponse
    {
        $session = PresensiKegiatan::findOrFail($session_id);

        if ($session->ustadz_id !== Auth::id() && ! Auth::user()->hasRole('admin')) {
            abort(403, 'Akses tidak sah ke sesi presensi ustadz lain.');
        }

        $rules = [];
        if ($request->has('presensi')) {
            $rules['presensi'] = ['required', 'array'];
            $rules['presensi.*'] = ['required', 'in:hadir,izin,sakit,alpha'];
        } elseif ($request->has('kehadiran')) {
            $rules['kehadiran'] = ['required', 'array'];
            $rules['kehadiran.*'] = ['required', 'in:hadir,izin,sakit,alpha'];
        } else {
            $rules['presensi'] = ['required', 'array'];
        }

        $request->validate($rules);

        $attendanceData = $request->input('presensi', $request->input('kehadiran', []));

        foreach ($attendanceData as $santriId => $status) {
            PresensiSantri::updateOrCreate(
                [
                    'presensi_kegiatan_id' => $session->id,
                    'santri_id' => $santriId,
                ],
                [
                    'status' => $status,
                ]
            );
        }

        return redirect()->back()->with('success', 'Presensi santri berhasil dicatat.');
    }
}
