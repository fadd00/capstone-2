<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KegiatanPondok;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KegiatanController extends Controller
{
    public function index(): View
    {
        $kegiatanList = KegiatanPondok::latest('id')->get();
        $kegiatan = $kegiatanList;

        return view('admin.kegiatan.index', compact('kegiatanList', 'kegiatan'));
    }

    public function create(): View
    {
        return view('admin.kegiatan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'tingkatan' => ['required', 'in:semua,tsanawiyah,aliyah,takhassus'],
            'kelas' => ['nullable', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        KegiatanPondok::create($validated);

        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan pondok berhasil ditambahkan.');
    }

    public function show(int $id): View
    {
        $kegiatan = KegiatanPondok::with('ustadz')->findOrFail($id);

        return view('admin.kegiatan.show', compact('kegiatan'));
    }

    public function edit(int $id): View
    {
        $kegiatan = KegiatanPondok::findOrFail($id);

        return view('admin.kegiatan.edit', compact('kegiatan'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $kegiatan = KegiatanPondok::findOrFail($id);

        $validated = $request->validate([
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'tingkatan' => ['required', 'in:semua,tsanawiyah,aliyah,takhassus'],
            'kelas' => ['nullable', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $kegiatan->update($validated);

        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan pondok berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $kegiatan = KegiatanPondok::findOrFail($id);
        $kegiatan->ustadz()->detach();
        $kegiatan->delete();

        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan pondok berhasil dihapus.');
    }
}
