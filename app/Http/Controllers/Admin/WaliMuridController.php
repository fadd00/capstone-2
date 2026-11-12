<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class WaliMuridController extends Controller
{
    public function index(): View
    {
        Role::firstOrCreate(['name' => 'wali_murid', 'guard_name' => 'web']);
        $waliMuridList = User::role('wali_murid')->with('santri')->latest('id')->get();
        $waliMurid = $waliMuridList;

        return view('admin.wali-murid.index', compact('waliMuridList', 'waliMurid'));
    }

    public function create(): View
    {
        return view('admin.wali-murid.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'min:6'],
        ]);

        Role::firstOrCreate(['name' => 'wali_murid', 'guard_name' => 'web']);
        $wali = User::create($validated);
        $wali->assignRole('wali_murid');

        return redirect()->route('admin.wali-murid.index')->with('success', 'Akun wali murid berhasil dibuat.');
    }

    public function show(int $id): View
    {
        Role::firstOrCreate(['name' => 'wali_murid', 'guard_name' => 'web']);
        $wali = User::role('wali_murid')->with('santri')->findOrFail($id);

        return view('admin.wali-murid.show', compact('wali'));
    }

    public function edit(int $id): View
    {
        Role::firstOrCreate(['name' => 'wali_murid', 'guard_name' => 'web']);
        $wali = User::role('wali_murid')->findOrFail($id);

        return view('admin.wali-murid.edit', compact('wali'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        Role::firstOrCreate(['name' => 'wali_murid', 'guard_name' => 'web']);
        $wali = User::role('wali_murid')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($wali->id)],
            'password' => ['nullable', 'string', 'confirmed', 'min:6'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $wali->update($validated);

        return redirect()->route('admin.wali-murid.index')->with('success', 'Data wali murid berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Role::firstOrCreate(['name' => 'wali_murid', 'guard_name' => 'web']);
        $wali = User::role('wali_murid')->findOrFail($id);
        $wali->santri()->update(['wali_murid_id' => null]);
        $wali->delete();

        return redirect()->route('admin.wali-murid.index')->with('success', 'Akun wali murid berhasil dihapus.');
    }
}
