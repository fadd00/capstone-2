<?php

namespace App\Http\Controllers\Ustadz;

use App\Http\Controllers\Controller;
use App\Models\PresensiKegiatan;
use App\Services\GeolocationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PresensiUstadzController extends Controller
{
    public function __construct(
        protected GeolocationService $geoService
    ) {}

    public function dashboard(): View
    {
        $kegiatanList = auth()->user()->kegiatanPondok;

        return view('ustadz.dashboard', compact('kegiatanList'));
    }

    public function index(): View
    {
        $sessions = PresensiKegiatan::with('kegiatanPondok')
            ->where('ustadz_id', auth()->id())
            ->latest('tanggal')
            ->get();

        return view('ustadz.presensi.index', compact('sessions'));
    }

    public function create(): View
    {
        $kegiatanList = auth()->user()->kegiatanPondok;

        return view('ustadz.presensi.create', compact('kegiatanList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kegiatan_pondok_id' => ['required', 'exists:kegiatan_pondok,id'],
            'tanggal' => ['required', 'date'],
            'hari' => ['nullable', 'string', 'max:20'],
            'jam_mulai' => ['required'],
            'jam_selesai' => ['nullable'],
            'materi' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $ustadz = $request->user();
        $isAssigned = $ustadz->kegiatanPondok()->where('kegiatan_pondok.id', $validated['kegiatan_pondok_id'])->exists();
        if (! $isAssigned) {
            abort(403, 'Kegiatan tidak ditugaskan kepada Anda.');
        }

        $alreadyCheckedIn = PresensiKegiatan::where('kegiatan_pondok_id', $validated['kegiatan_pondok_id'])
            ->where('ustadz_id', $ustadz->id)
            ->where('tanggal', $validated['tanggal'])
            ->exists();

        if ($alreadyCheckedIn) {
            return back()->withErrors(['kegiatan_pondok_id' => 'Presensi untuk kegiatan ini pada tanggal yang sama sudah tercatat.']);
        }

        $distance = $this->geoService->calculateDistance(
            (float) config('services.pesantren.lat', GeolocationService::PESANTREN_LAT),
            (float) config('services.pesantren.lng', GeolocationService::PESANTREN_LNG),
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );

        $status = $this->geoService->determineStatus(
            $distance,
            (float) config('services.pesantren.radius', GeolocationService::DEFAULT_RADIUS_METERS)
        );

        $hari = $validated['hari'] ?? null;
        if (empty($hari)) {
            $dayMap = [
                0 => 'Minggu',
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
            ];
            $hari = $dayMap[Carbon::parse($validated['tanggal'])->dayOfWeek];
        }

        PresensiKegiatan::create([
            'kegiatan_pondok_id' => $validated['kegiatan_pondok_id'],
            'ustadz_id' => $ustadz->id,
            'tanggal' => $validated['tanggal'],
            'hari' => $hari,
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'] ?? null,
            'materi' => $validated['materi'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'status' => $status,
        ]);

        return redirect()->route('ustadz.presensi-kegiatan.index')->with('success', 'Presensi kegiatan berhasil dicatat.');
    }

    public function show(int $id): View
    {
        $session = PresensiKegiatan::with(['kegiatanPondok', 'presensiSantri.santri'])->findOrFail($id);

        if ($session->ustadz_id !== auth()->id() && ! auth()->user()->hasRole('admin')) {
            abort(403, 'Akses tidak sah ke sesi presensi ustadz lain.');
        }

        return view('ustadz.presensi.show', compact('session'));
    }
}
