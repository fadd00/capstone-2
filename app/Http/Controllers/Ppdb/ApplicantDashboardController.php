<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ApplicantDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $pendaftaran = Pendaftaran::where('user_id', $user->id)->first();

        return view('user.dashboard', compact('pendaftaran', 'user'));
    }

    public function downloadFile(int $id, string $field): Response
    {
        $pendaftaran = Pendaftaran::findOrFail($id);

        if ($pendaftaran->user_id !== Auth::id()) {
            abort(403, 'Akses tidak sah ke berkas pendaftar.');
        }

        if (! in_array($field, Pendaftaran::ALLOWED_FILES, true)) {
            abort(404, 'Kolom berkas tidak valid.');
        }

        $filePath = $pendaftaran->{$field};
        if (! $filePath || ! Storage::disk('local')->exists($filePath)) {
            abort(404, 'Berkas tidak ditemukan.');
        }

        return Storage::disk('local')->response($filePath);
    }
}
