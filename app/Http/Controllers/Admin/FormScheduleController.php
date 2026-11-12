<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StatusForm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormScheduleController extends Controller
{
    public function index()
    {
        $statusForm = StatusForm::first() ?? StatusForm::create([
            'status' => 'Buka',
        ]);

        return view('admin.status-form', compact('statusForm'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Buka,Tutup'],
            'tanggal_buka' => ['nullable', 'date'],
            'tanggal_tutup' => ['nullable', 'date', 'after_or_equal:tanggal_buka'],
        ]);

        $statusForm = StatusForm::first();
        if (! $statusForm) {
            $statusForm = StatusForm::create(['status' => $validated['status']]);
        }

        DB::table('status_forms')->where('id', $statusForm->id)->update([
            'status' => $validated['status'],
            'tanggal_buka' => $validated['tanggal_buka'] ?? null,
            'tanggal_tutup' => $validated['tanggal_tutup'] ?? null,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Jadwal pendaftaran berhasil diperbarui.');
    }
}
