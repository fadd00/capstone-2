<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class SantriController extends Controller
{
    public function index(Request $request): View
    {
        $query = Santri::with(['waliMurid', 'ustadzPembina']);

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $santriList = $query->latest('id')->get();
        $santri = $santriList;

        return view('admin.santri.index', compact('santriList', 'santri'));
    }

    public function create(): View
    {
        Role::firstOrCreate(['name' => 'wali_murid', 'guard_name' => 'web']);
        $waliMuridList = User::role('wali_murid')->orderBy('name')->get();

        return view('admin.santri.create', compact('waliMuridList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:50', 'unique:santri,nis'],
            'tingkatan' => ['required', 'in:tsanawiyah,aliyah,takhassus'],
            'kelas' => ['nullable', 'string', 'max:50'],
            'wali_murid_id' => ['nullable', 'exists:users,id'],
            'tanggal_lahir' => ['nullable', 'date'],
        ]);

        $santri = new Santri($validated);
        if (! empty($validated['tanggal_lahir'])) {
            $santri->setRawAttributes(array_merge($santri->getAttributes(), ['tanggal_lahir' => $validated['tanggal_lahir']]));
        }
        $santri->save();

        return redirect()->route('admin.santri.index')->with('success', 'Santri berhasil ditambahkan.');
    }

    public function show(int $id): View
    {
        $santri = Santri::with(['waliMurid', 'ustadzPembina'])->findOrFail($id);

        return view('admin.santri.show', compact('santri'));
    }

    public function edit(int $id): View
    {
        $santri = Santri::findOrFail($id);
        Role::firstOrCreate(['name' => 'wali_murid', 'guard_name' => 'web']);
        $waliMuridList = User::role('wali_murid')->orderBy('name')->get();

        return view('admin.santri.edit', compact('santri', 'waliMuridList'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $santri = Santri::findOrFail($id);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:50', Rule::unique('santri', 'nis')->ignore($santri->id)],
            'tingkatan' => ['required', 'in:tsanawiyah,aliyah,takhassus'],
            'kelas' => ['nullable', 'string', 'max:50'],
            'wali_murid_id' => ['nullable', 'exists:users,id'],
            'tanggal_lahir' => ['nullable', 'date'],
        ]);

        $santri->fill($validated);
        if (! empty($validated['tanggal_lahir'])) {
            $santri->setRawAttributes(array_merge($santri->getAttributes(), ['tanggal_lahir' => $validated['tanggal_lahir']]));
        }
        $santri->save();

        return redirect()->route('admin.santri.index')->with('success', 'Data santri berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $santri = Santri::findOrFail($id);
        $santri->ustadzPembina()->detach();
        $santri->delete();

        return redirect()->route('admin.santri.index')->with('success', 'Santri berhasil dihapus.');
    }
}
