<?php

namespace App\Http\Controllers\Ustadz;

use App\Http\Controllers\Controller;
use App\Models\Murojaah;
use App\Models\SetoranHafalan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TahfidzController extends Controller
{
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
        $validator = Validator::make($request->all(), [
            'santri_id' => ['required', 'integer', 'exists:santri,id'],
            'tanggal' => ['required', 'date'],
            'juz' => ['required', 'integer', 'min:1', 'max:30'],
            'surat' => ['required', 'string', 'max:100', Rule::in(config('quran.surah'))],
            'ayat' => ['required', 'string', 'max:50'],
            'nilai' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status_selesai' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $surat = trim((string) $request->input('surat', ''));
            $ayat = trim((string) $request->input('ayat', ''));

            if ($surat === '') {
                $validator->errors()->add('ayat', 'Ayat memerlukan nama surat yang valid.');
            }
            if ($ayat === '') {
                $validator->errors()->add('surat', 'Surat memerlukan rentang ayat yang valid.');
            }
        });

        $validated = $validator->validate();

        if (! $request->user()->santriBinaan()->where('santri.id', $validated['santri_id'])->exists()) {
            abort(403, 'Santri bukan binaan ustadz ini.');
        }

        SetoranHafalan::create([
            'santri_id' => $validated['santri_id'],
            'ustadz_id' => $request->user()->id,
            'tanggal' => $validated['tanggal'],
            'juz' => $validated['juz'],
            'surat' => trim($validated['surat']),
            'ayat' => trim($validated['ayat']),
            'nilai' => $validated['nilai'] ?? null,
            'status_selesai' => (bool) ($validated['status_selesai'] ?? false),
            'catatan' => $validated['catatan'] ?? null,
        ]);

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
        $validated = $request->validate([
            'santri_id' => ['required', 'integer', 'exists:santri,id'],
            'tanggal' => ['required', 'date'],
            'juz' => ['required', 'integer', 'min:1', 'max:30'],
            'surat' => ['required', 'string', 'max:100', Rule::in(config('quran.surah'))],
            'ayat' => ['required', 'string', 'max:50'],
            'nilai' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status_selesai' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! $request->user()->santriBinaan()->where('santri.id', $validated['santri_id'])->exists()) {
            abort(403, 'Santri bukan binaan ustadz ini.');
        }

        Murojaah::create([
            'santri_id' => $validated['santri_id'],
            'ustadz_id' => $request->user()->id,
            'tanggal' => $validated['tanggal'],
            'juz' => $validated['juz'],
            'surat' => trim($validated['surat']),
            'ayat' => trim($validated['ayat']),
            'nilai' => $validated['nilai'] ?? null,
            'status_selesai' => (bool) ($validated['status_selesai'] ?? false),
        ]);

        return redirect()->route('ustadz.murojaah.index')->with('success', 'Murojaah hafalan berhasil dicatat.');
    }

    public function showMurojaah(int $id): View
    {
        $murojaah = Murojaah::with(['santri', 'ustadz'])->findOrFail($id);
        $this->authorizeTahfidzRecord($murojaah->ustadz_id, $murojaah->santri_id);

        return view('ustadz.tahfidz.murojaah.show', compact('murojaah'));
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
