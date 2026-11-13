<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PpdbExport;
use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Models\User;
use App\Services\FonnteService;
use App\Services\PendaftarPromotionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

class PpdbController extends Controller
{
    public function index(Request $request)
    {
        $query = Pendaftaran::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        $pendaftarans = $query->paginate(15)->withQueryString();

        $total = Pendaftaran::count();
        $menunggu = Pendaftaran::where('status', 'Menunggu')->count();
        $lulus = Pendaftaran::where('status', 'Lulus')->count();
        $tidakLulus = Pendaftaran::where('status', 'Tidak Lulus')->count();
        $lakiLaki = Pendaftaran::where('jenis_kelamin', 'Laki-laki')->count();
        $perempuan = Pendaftaran::where('jenis_kelamin', 'Perempuan')->count();

        return view('admin.ppdb.index', compact(
            'pendaftarans',
            'total',
            'menunggu',
            'lulus',
            'tidakLulus',
            'lakiLaki',
            'perempuan'
        ));
    }

    public function downloadFile(int $id, string $field): Response
    {
        if (! in_array($field, Pendaftaran::ALLOWED_FILES, true)) {
            abort(404, 'Kolom berkas tidak valid.');
        }

        $pendaftaran = Pendaftaran::findOrFail($id);

        $filePath = $pendaftaran->{$field};
        if (! $filePath || ! Storage::disk('local')->exists($filePath)) {
            abort(404, 'Berkas tidak ditemukan.');
        }

        return Storage::disk('local')->response($filePath);
    }

    public function verifikasi(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Menunggu,Lulus,Tidak Lulus'],
            'catatan' => ['nullable', 'string'],
        ]);

        $pendaftaran = Pendaftaran::findOrFail($id);
        $pendaftaran->update(['status' => $validated['status']]);

        if ($pendaftaran->no_hp && in_array($validated['status'], ['Lulus', 'Tidak Lulus'], true)) {
            $catatanText = ! empty($validated['catatan']) ? " Catatan: {$validated['catatan']}" : '';
            $message = "Assalamu'alaikum {$pendaftaran->nama_lengkap}, status pendaftaran PPDB Anda di PPTQ Muhammadiyah Ibnu Juraimi dinyatakan: {$validated['status']}.{$catatanText}";

            app(FonnteService::class)->sendMessage($pendaftaran->no_hp, $message);
        }

        return back()->with('success', 'Status pendaftaran berhasil diperbarui.');
    }

    public function notifikasi()
    {
        return view('admin.notifikasi');
    }

    public function kirimNotifikasi(Request $request, FonnteService $fonnteService): RedirectResponse
    {
        $validated = $request->validate([
            'target_status' => ['required', 'string'],
            'pesan' => ['required', 'string'],
        ]);

        $targetStatus = $validated['target_status'];
        $query = Pendaftaran::query()->whereNotNull('no_hp')->where('no_hp', '!=', '');

        if ($targetStatus !== 'semua' && $targetStatus !== 'All') {
            $query->where('status', $targetStatus);
        }

        $recipients = $query->get();
        $count = 0;

        // shortcut: synchronous Fonnte loop; move to queued job if applicant count exceeds ~100
        foreach ($recipients as $recipient) {
            $fonnteService->sendMessage($recipient->no_hp, $validated['pesan']);
            $count++;
        }

        return back()->with('success', "Pesan berhasil dikirim ke {$count} pendaftar.");
    }

    public function downloadZip(int $id)
    {
        $pendaftar = Pendaftaran::findOrFail($id);

        $tempZip = tempnam(sys_get_temp_dir(), 'ppdb_zip_');
        $zip = new ZipArchive;

        if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat file arsip ZIP.');
        }

        $fileCount = 0;
        foreach (Pendaftaran::ALLOWED_FILES as $field) {
            $path = $pendaftar->{$field};
            if ($path && Storage::disk('local')->exists($path)) {
                $zip->addFromString(basename($path), Storage::disk('local')->get($path));
                $fileCount++;
            }
        }

        if ($fileCount === 0) {
            $zip->addFromString('CATATAN.txt', "Pendaftar {$pendaftar->nama_lengkap} belum memiliki berkas fisik.");
        }

        $zip->close();

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pendaftar->nama_lengkap);
        $fileName = "Berkas_{$safeName}_{$pendaftar->id}.zip";

        return response()->download($tempZip, $fileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function destroy(int $id): RedirectResponse
    {
        $pendaftar = Pendaftaran::findOrFail($id);

        foreach (Pendaftaran::ALLOWED_FILES as $field) {
            if ($pendaftar->{$field} && Storage::disk('local')->exists($pendaftar->{$field})) {
                Storage::disk('local')->delete($pendaftar->{$field});
            }
        }

        $user = $pendaftar->user;
        $pendaftar->delete();

        if ($user && $user->hasRole('calon_santri')) {
            $user->delete();
        }

        return redirect()->route('admin.ppdb.index')->with('success', 'Data pendaftar berhasil dihapus.');
    }

    public function destroyAll(): RedirectResponse
    {
        $paths = [];

        DB::transaction(function () use (&$paths) {
            foreach (Pendaftaran::all() as $pendaftar) {
                foreach (Pendaftaran::ALLOWED_FILES as $field) {
                    if ($pendaftar->{$field}) {
                        $paths[] = $pendaftar->{$field};
                    }
                }
            }

            User::role('calon_santri')->delete();
            Pendaftaran::query()->delete();
        });

        foreach ($paths as $path) {
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
        }

        return redirect()->route('admin.ppdb.index')->with('success', 'Semua data pendaftar berhasil dibersihkan.');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new PpdbExport($request->status), 'rekap-pendaftaran.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = Pendaftaran::query();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $pendaftarans = $query->get();

        $pdf = Pdf::loadView('admin.ppdb.pdf', compact('pendaftarans'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('rekap-pendaftaran.pdf');
    }

    public function promosikan(Request $request, int $id, PendaftarPromotionService $promotionService): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:50', 'unique:santri,nis'],
            'tingkatan' => ['required', 'in:tsanawiyah,aliyah,takhassus'],
            'kelas' => ['required', 'string'],
            'wali_murid_id' => ['nullable', 'exists:users,id'],
        ]);

        $pendaftar = Pendaftaran::findOrFail($id);

        if ($pendaftar->status !== 'Lulus') {
            return back()->withErrors(['status' => 'Pendaftar belum dinyatakan Lulus.']);
        }

        if ($pendaftar->santri_id !== null) {
            return back()->withErrors(['santri_id' => 'Pendaftar sudah dipromosikan sebelumnya.']);
        }

        try {
            $promotionService->promote(
                $pendaftar,
                $validated['nis'],
                $validated['tingkatan'],
                $validated['kelas'],
                $validated['wali_murid_id'] ?? null
            );
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Pendaftar berhasil dipromosikan ke Santri.');
    }
}
