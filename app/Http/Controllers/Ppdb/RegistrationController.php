<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Mail\RegisterSuccessMail;
use App\Models\Pendaftaran;
use App\Models\User;
use App\Services\DocumentStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegistrationController extends Controller
{
    public function __construct(
        protected DocumentStorageService $storageService
    ) {}

    public function create(): View
    {
        return view('public.pendaftaran');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'no_hp' => ['required', 'string', 'max:20'],
            'alamat' => ['required', 'string'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'nisn' => ['nullable', 'string', 'max:10'],
            'nik_santri' => ['nullable', 'string', 'max:255'],
            'no_kk' => ['nullable', 'string', 'max:255'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'nik_ayah' => ['nullable', 'string', 'max:255'],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:100'],
            'penghasilan_ayah' => ['nullable', 'string', 'max:255'],
            'no_hp_ayah' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'nik_ibu' => ['nullable', 'string', 'max:255'],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:100'],
            'penghasilan_ibu' => ['nullable', 'string', 'max:255'],
            'no_hp_ibu' => ['nullable', 'string', 'max:255'],
            'asal_sekolah' => ['nullable', 'string', 'max:255'],
            'alamat_asal_sekolah' => ['nullable', 'string', 'max:255'],
            'jumlah_hafalan' => ['nullable', 'string', 'max:100'],
            'prestasi' => ['nullable', 'string', 'max:255'],

            // 7 Mandatory files
            'pas_photo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'min:1', 'max:2048'],
            'bukti_transfer' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'min:1', 'max:2048'],
            'formulir_pendaftaran' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'min:1', 'max:2048'],
            'surat_pernyataan' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'min:1', 'max:2048'],
            'akta_kelahiran' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'min:1', 'max:2048'],
            'kartu_keluarga' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'min:1', 'max:2048'],
            'raport_terakhir' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'min:1', 'max:2048'],

            // 3 Optional files
            'ijazah_terakhir' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'min:1', 'max:2048'],
            'skhun' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'min:1', 'max:2048'],
            'sktm' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'min:1', 'max:2048'],
        ]);

        $randomPassword = Str::random(8);

        // Upload files to private storage
        $filePaths = [];
        foreach (Pendaftaran::ALLOWED_FILES as $field) {
            if ($request->hasFile($field)) {
                $filePaths[$field] = $this->storageService->store($request->file($field), $field);
            } else {
                $filePaths[$field] = null;
            }
        }

        DB::transaction(function () use ($validated, $filePaths, $randomPassword) {
            Role::firstOrCreate(['name' => 'calon_santri', 'guard_name' => 'web']);

            $user = User::create([
                'name' => $validated['nama_lengkap'],
                'email' => $validated['email'],
                'password' => $randomPassword,
            ]);
            $user->assignRole('calon_santri');

            $pendaftaranData = array_merge($validated, $filePaths, [
                'user_id' => $user->id,
                'status' => 'Menunggu',
                'tanggal_daftar' => now(),
            ]);

            Pendaftaran::create($pendaftaranData);
        });

        // Dispatch transactional credentials email
        Mail::to($validated['email'])->send(
            new RegisterSuccessMail($validated['email'], $randomPassword, $validated['nama_lengkap'])
        );

        // Flash credentials to session for one-time display
        session()->flash('email', $validated['email']);
        session()->flash('password', $randomPassword);

        return redirect()->route('register.success');
    }

    public function success(): View
    {
        return view('public.register-success', [
            'email' => session('email'),
            'password' => session('password'),
        ]);
    }
}
