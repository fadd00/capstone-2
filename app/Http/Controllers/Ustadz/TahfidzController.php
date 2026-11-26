<?php

namespace App\Http\Controllers\Ustadz;

use App\Http\Controllers\Controller;
use App\Models\Murojaah;
use App\Models\SetoranHafalan;
use App\Services\QuranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TahfidzController extends Controller
{
    public function __construct(
        protected QuranService $quran
    ) {}

    public function indexSetoran(Request $request): View
    {
        $user = $request->user();
        $setoranList = SetoranHafalan::with('santri')
            ->where('ustadz_id', $user->id)
            ->latest('tanggal')
            ->latest('id')
            ->paginate(15);

        return view('ustadz.tahfidz.setoran.index', compact('setoranList'));
    }

    public function createSetoran(Request $request): View
    {
        $santriBinaan = $request->user()->santriBinaan()->orderBy('nama')->get();
        $surahList = config('quran.surah');

        return view('ustadz.tahfidz.setoran.create', compact('santriBinaan', 'surahList'));
    }

    public function storeSetoran(Request $request): RedirectResponse
    {
        SetoranHafalan::create($this->validatedTahfidz($request));

        return redirect()->route('ustadz.setoran-hafalan.index')->with('success', 'Setoran hafalan baru berhasil dicatat.');
    }

    public function showSetoran(int $id): View
    {
        $setoran = SetoranHafalan::with(['santri', 'ustadz'])->findOrFail($id);
        $this->authorizeTahfidzRecord($setoran->ustadz_id, $setoran->santri_id);

        return view('ustadz.tahfidz.setoran.show', compact('setoran'));
    }

    public function indexMurojaah(Request $request): View
    {
        $user = $request->user();
        $murojaahList = Murojaah::with('santri')
            ->where('ustadz_id', $user->id)
            ->latest('tanggal')
            ->latest('id')
            ->paginate(15);

        return view('ustadz.tahfidz.murojaah.index', compact('murojaahList'));
    }

    public function createMurojaah(Request $request): View
    {
        $santriBinaan = $request->user()->santriBinaan()->orderBy('nama')->get();
        $surahList = config('quran.surah');

        return view('ustadz.tahfidz.murojaah.create', compact('santriBinaan', 'surahList'));
    }

    public function storeMurojaah(Request $request): RedirectResponse
    {
        Murojaah::create($this->validatedTahfidz($request));

        return redirect()->route('ustadz.murojaah.index')->with('success', 'Murojaah hafalan berhasil dicatat.');
    }

    public function showMurojaah(int $id): View
    {
        $murojaah = Murojaah::with(['santri', 'ustadz'])->findOrFail($id);
        $this->authorizeTahfidzRecord($murojaah->ustadz_id, $murojaah->santri_id);

        return view('ustadz.tahfidz.murojaah.show', compact('murojaah'));
    }

    /**
     * Validasi bersama setoran & murojaah. Alurnya: surat -> ayat mulai/selesai
     * (dibatasi jumlah ayat surat) -> juz dihitung server dari ayat mulai.
     */
    private function validatedTahfidz(Request $request): array
    {
        $validated = $request->validate([
            'santri_id' => ['required', 'integer', 'exists:santri,id'],
            'tanggal' => ['required', 'date'],
            'surat' => ['required', 'string', Rule::in(config('quran.surah'))],
            'ayat_mulai' => ['required', 'integer', 'min:1'],
            'ayat_selesai' => ['nullable', 'integer', 'gte:ayat_mulai'],
            'nilai' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status_selesai' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $surahNumber = $this->quran->surahNumber($validated['surat']);
        $jumlahAyat = $this->quran->ayatCount($surahNumber);
        $mulai = (int) $validated['ayat_mulai'];
        $selesai = (int) ($validated['ayat_selesai'] ?? $mulai);

        foreach (['ayat_mulai' => $mulai, 'ayat_selesai' => $selesai] as $field => $ayat) {
            if ($ayat > $jumlahAyat) {
                throw ValidationException::withMessages([
                    $field => "Surat {$validated['surat']} hanya memiliki {$jumlahAyat} ayat.",
                ]);
            }
        }

        if (! $request->user()->santriBinaan()->where('santri.id', $validated['santri_id'])->exists()) {
            abort(403, 'Santri bukan binaan ustadz ini.');
        }

        return [
            'santri_id' => $validated['santri_id'],
            'ustadz_id' => $request->user()->id,
            'tanggal' => $validated['tanggal'],
            'juz' => $this->quran->juzOf($surahNumber, $mulai),
            'surat' => $validated['surat'],
            'ayat' => $mulai === $selesai ? (string) $mulai : "{$mulai}-{$selesai}",
            'nilai' => $validated['nilai'] ?? null,
            'status_selesai' => (bool) ($validated['status_selesai'] ?? false),
            'catatan' => $validated['catatan'] ?? null,
        ];
    }

    private function authorizeTahfidzRecord(int $ustadzId, int $santriId): void
    {
        $user = auth()->user();
        $isBinaan = $user->santriBinaan()->where('santri.id', $santriId)->exists();

        if ($ustadzId !== $user->id && ! $isBinaan) {
            abort(403, 'Akses ditolak.');
        }
    }
}
