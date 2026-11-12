<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KegiatanPondok;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class PenugasanKegiatanController extends Controller
{
    public function index(): View
    {
        Role::firstOrCreate(['name' => 'ustadz', 'guard_name' => 'web']);
        $ustadzList = User::role('ustadz')->with('kegiatanPondok')->orderBy('name')->get();
        $kegiatanList = KegiatanPondok::orderBy('nama_kegiatan')->get();

        return view('admin.penugasan.kegiatan', compact('ustadzList', 'kegiatanList'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'kegiatan_ids' => ['nullable', 'array'],
            'kegiatan_ids.*' => ['exists:kegiatan_pondok,id'],
        ]);

        $ustadz = User::findOrFail($id);
        $ustadz->kegiatanPondok()->sync($request->input('kegiatan_ids', []));

        return redirect()->back()->with('success', 'Penugasan kegiatan berhasil disimpan.');
    }
}
