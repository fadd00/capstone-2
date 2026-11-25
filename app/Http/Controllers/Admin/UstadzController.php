<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UstadzController extends Controller
{
    public function index(): View
    {
        Role::firstOrCreate(['name' => 'ustadz', 'guard_name' => 'web']);
        $ustadzList = User::role('ustadz')->latest('id')->get();
        $ustadz = $ustadzList;

        return view('admin.ustadz.index', compact('ustadzList', 'ustadz'));
    }

    public function create(): View
    {
        return view('admin.ustadz.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'min:6'],
        ]);

        Role::firstOrCreate(['name' => 'ustadz', 'guard_name' => 'web']);
        $ustadz = User::create($validated);
        $ustadz->assignRole('ustadz');

        return redirect()->route('admin.ustadz.index')->with('success', 'Akun ustadz berhasil dibuat.');
    }

    public function show(int $id): View
    {
        Role::firstOrCreate(['name' => 'ustadz', 'guard_name' => 'web']);
        $ustadz = User::role('ustadz')->with(['kegiatanPondok', 'santriBinaan'])->findOrFail($id);

        return view('admin.ustadz.show', compact('ustadz'));
    }

    public function edit(int $id): View
    {
        Role::firstOrCreate(['name' => 'ustadz', 'guard_name' => 'web']);
        $ustadz = User::role('ustadz')->findOrFail($id);

        return view('admin.ustadz.edit', compact('ustadz'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        Role::firstOrCreate(['name' => 'ustadz', 'guard_name' => 'web']);
        $ustadz = User::role('ustadz')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ustadz->id)],
            'password' => ['nullable', 'string', 'confirmed', 'min:6'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $ustadz->update($validated);

        return redirect()->route('admin.ustadz.index')->with('success', 'Data ustadz berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Role::firstOrCreate(['name' => 'ustadz', 'guard_name' => 'web']);
        $ustadz = User::role('ustadz')->findOrFail($id);

        if ($reason = $ustadz->deletionBlockedReason()) {
            return redirect()->route('admin.ustadz.index')->with('error', $reason);
        }

        $ustadz->kegiatanPondok()->detach();
        $ustadz->santriBinaan()->detach();
        $ustadz->delete();

        return redirect()->route('admin.ustadz.index')->with('success', 'Akun ustadz berhasil dihapus.');
    }
}
