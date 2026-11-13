<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function index()
    {
        $admins = User::role('admin')->latest()->get();

        return view('admin.akun.index', compact('admins'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->assignRole('admin');

        return redirect()->route('admin.akun.index')->with('success', 'Akun administrator baru berhasil dibuat.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $admin = User::role('admin')->findOrFail($id);

        if ((int) Auth::id() === $admin->id) {
            return redirect()->route('admin.akun.index')->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $deleted = DB::transaction(function () use ($admin): bool {
            if (User::role('admin')->lockForUpdate()->count() <= 1) {
                return false;
            }

            $admin->delete();

            return true;
        });

        if (! $deleted) {
            return redirect()->route('admin.akun.index')->with('error', 'Tidak dapat menghapus satu-satunya administrator.');
        }

        return redirect()->route('admin.akun.index')->with('success', 'Akun administrator berhasil dihapus.');
    }

    public function resetPassword(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $admin = User::role('admin')->findOrFail($id);
        $admin->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'Password administrator berhasil diperbarui.');
    }
}
