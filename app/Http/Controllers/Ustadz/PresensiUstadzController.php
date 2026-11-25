<?php

namespace App\Http\Controllers\Ustadz;

use App\Http\Controllers\Controller;
use App\Models\PresensiKegiatan;
use App\Models\User;
use App\Services\GeolocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class PresensiUstadzController extends Controller
{
    public function __construct(
        protected GeolocationService $geoService
    ) {}

    public function dashboard(Request $request): View
    {
        /** @var User $ustadz */
        $ustadz = $request->user();
        $kegiatanList = $ustadz->kegiatanPondok;

        return view('ustadz.dashboard', compact('kegiatanList'));
    }

    public function index(Request $request): View
    {
        $sessions = PresensiKegiatan::with('kegiatanPondok')
            ->where('ustadz_id', $request->user()->id)
            ->latest('tanggal')
            ->get();

        return view('ustadz.presensi.index', compact('sessions'));
    }

    public function create(Request $request): View
    {
        /** @var User $ustadz */
        $ustadz = $request->user();
        $kegiatanList = $ustadz->kegiatanPondok;

        return view('ustadz.presensi.create', compact('kegiatanList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $checkInTime = now();

        $validated = $request->validate([
            'kegiatan_pondok_id' => ['required', 'exists:kegiatan_pondok,id'],
            'jam_selesai' => ['nullable', 'date_format:H:i'],
            'materi' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'foto_bukti' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        /** @var User $ustadz */
        $ustadz = $request->user();
        $isAssigned = $ustadz->kegiatanPondok()->where('kegiatan_pondok.id', $validated['kegiatan_pondok_id'])->exists();
        if (! $isAssigned) {
            abort(403, 'Kegiatan tidak ditugaskan kepada Anda.');
        }

        $alreadyCheckedIn = PresensiKegiatan::where('kegiatan_pondok_id', $validated['kegiatan_pondok_id'])
            ->where('ustadz_id', $ustadz->id)
            ->where('tanggal', $checkInTime->toDateString())
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

        $hari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ][$checkInTime->format('l')];

        $fotoPath = Storage::disk('local')->putFile('presensi-kegiatan', $validated['foto_bukti']);
        if ($fotoPath === false) {
            throw new RuntimeException('Gagal menyimpan foto bukti presensi.');
        }

        try {
            PresensiKegiatan::create([
                'kegiatan_pondok_id' => $validated['kegiatan_pondok_id'],
                'ustadz_id' => $ustadz->id,
                'tanggal' => $checkInTime->toDateString(),
                'hari' => $hari,
                'jam_mulai' => $checkInTime->format('H:i:s'),
                'jam_selesai' => $validated['jam_selesai'] ?? null,
                'materi' => $validated['materi'] ?? null,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'foto_bukti' => $fotoPath,
                'status' => $status,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($fotoPath);
            throw $exception;
        }

        return redirect()->route('ustadz.presensi-kegiatan.index')->with('success', 'Presensi kegiatan berhasil dicatat.');
    }

    public function show(Request $request, int $id): View
    {
        $session = PresensiKegiatan::with(['kegiatanPondok', 'presensiSantri.santri'])->findOrFail($id);

        /** @var User $user */
        $user = $request->user();
        if ($session->ustadz_id !== $user->id && ! $user->hasRole('admin')) {
            abort(403, 'Akses tidak sah ke sesi presensi ustadz lain.');
        }

        return view('ustadz.presensi.show', compact('session'));
    }

    public function photo(Request $request, int $id): BinaryFileResponse
    {
        $session = PresensiKegiatan::findOrFail($id);

        /** @var User $user */
        $user = $request->user();
        if (! $user->hasRole('admin') && (! $user->hasRole('ustadz') || $session->ustadz_id !== $user->id)) {
            abort(403, 'Akses tidak sah ke foto bukti presensi.');
        }

        abort_unless($session->foto_bukti, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($session->foto_bukti), 404);

        return response()->file($disk->path($session->foto_bukti), [
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}