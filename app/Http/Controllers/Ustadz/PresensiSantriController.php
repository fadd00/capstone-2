<?php

namespace App\Http\Controllers\Ustadz;

use App\Http\Controllers\Controller;
use App\Models\PresensiKegiatan;
use App\Models\PresensiSantri;
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

        $santriList = $session->kegiatanPondok->santriPeserta()->orderBy('nama')->get();

        $existingAttendance = PresensiSantri::where('presensi_kegiatan_id', $session->id)
            ->pluck('status', 'santri_id')
            ->toArray();

        return view('ustadz.presensi.santri', compact('session', 'santriList', 'existingAttendance'));
    }

    public function store(Request $request, int $session_id): RedirectResponse
    {
        $session = PresensiKegiatan::with('kegiatanPondok')->findOrFail($session_id);

        if ($session->ustadz_id !== Auth::id() && ! Auth::user()->hasRole('admin')) {
            abort(403, 'Akses tidak sah ke sesi presensi ustadz lain.');
        }

        if ($session->status === 'invalid') {
            return back()->withErrors(['presensi' => 'Sesi ini berada di luar radius pesantren sehingga presensi santri tidak dapat dicatat.']);
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
        $santriIds = array_map('intval', array_keys($attendanceData));

        if ($session->kegiatanPondok->santriPeserta()->whereIn('id', $santriIds)->count() !== count(array_unique($santriIds))) {
            return back()->withErrors(['presensi' => 'Terdapat santri yang tidak valid atau bukan peserta kegiatan ini.']);
        }

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
