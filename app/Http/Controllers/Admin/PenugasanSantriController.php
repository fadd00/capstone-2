<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class PenugasanSantriController extends Controller
{
    public function index(): View
    {
        Role::firstOrCreate(['name' => 'ustadz', 'guard_name' => 'web']);
        $ustadzList = User::role('ustadz')->with('santriBinaan')->orderBy('name')->get();
        $santriList = Santri::orderBy('nama')->get();

        return view('admin.penugasan.santri', compact('ustadzList', 'santriList'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'santri_ids' => ['nullable', 'array'],
            'santri_ids.*' => ['exists:santri,id'],
        ]);

        $ustadz = User::findOrFail($id);
        $ustadz->santriBinaan()->sync($request->input('santri_ids', []));

        return redirect()->back()->with('success', 'Penugasan santri binaan berhasil disimpan.');
    }
}
