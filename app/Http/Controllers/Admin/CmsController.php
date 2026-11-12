<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carousel;
use App\Models\Informasi;
use App\Models\Keunggulan;
use App\Models\Kontak;
use App\Models\Statistik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CmsController extends Controller
{
    // ==================== CAROUSEL ====================
    public function carouselIndex()
    {
        $carousels = Carousel::orderBy('order')->get();

        return view('admin.carousel.index', compact('carousels'));
    }

    public function carouselStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'file', 'image', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer'],
        ]);

        $path = $request->file('image')->store('carousel', 'public');

        Carousel::create([
            'image' => $path,
            'caption' => $validated['caption'] ?? '',
            'order' => $validated['order'] ?? 0,
        ]);

        return back()->with('success', 'Banner carousel berhasil ditambahkan.');
    }

    public function carouselUpdate(Request $request, int $id): RedirectResponse
    {
        $carousel = Carousel::findOrFail($id);

        $validated = $request->validate([
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer'],
        ]);

        $data = [
            'caption' => $validated['caption'] ?? $carousel->caption,
            'order' => $validated['order'] ?? $carousel->order,
        ];

        if ($request->hasFile('image')) {
            if ($carousel->image && Storage::disk('public')->exists($carousel->image)) {
                Storage::disk('public')->delete($carousel->image);
            }
            $data['image'] = $request->file('image')->store('carousel', 'public');
        }

        $carousel->update($data);

        return back()->with('success', 'Banner carousel berhasil diperbarui.');
    }

    public function carouselDestroy(int $id): RedirectResponse
    {
        $carousel = Carousel::findOrFail($id);

        if ($carousel->image && Storage::disk('public')->exists($carousel->image)) {
            Storage::disk('public')->delete($carousel->image);
        }

        $carousel->delete();

        return back()->with('success', 'Banner carousel berhasil dihapus.');
    }

    // ==================== KEUNGGULAN ====================
    public function keunggulanIndex()
    {
        $keunggulans = Keunggulan::orderBy('order')->get();

        return view('admin.keunggulan.index', compact('keunggulans'));
    }

    public function keunggulanStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi_singkat' => ['required', 'string', 'max:500'],
            'deskripsi_detail' => ['required', 'string'],
            'order' => ['nullable', 'integer'],
        ]);

        Keunggulan::create($validated);

        return back()->with('success', 'Keunggulan berhasil ditambahkan.');
    }

    public function keunggulanUpdate(Request $request, int $id): RedirectResponse
    {
        $keunggulan = Keunggulan::findOrFail($id);

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi_singkat' => ['required', 'string', 'max:500'],
            'deskripsi_detail' => ['required', 'string'],
            'order' => ['nullable', 'integer'],
        ]);

        $keunggulan->update($validated);

        return back()->with('success', 'Keunggulan berhasil diperbarui.');
    }

    public function keunggulanDestroy(int $id): RedirectResponse
    {
        $keunggulan = Keunggulan::findOrFail($id);
        $keunggulan->delete();

        return back()->with('success', 'Keunggulan berhasil dihapus.');
    }

    // ==================== STATISTIK ====================
    public function statistikEdit()
    {
        $statistik = Statistik::first() ?? Statistik::create([
            'santri_aktif' => 0,
            'asatidzah_bersanad' => 0,
            'tahun_berkhidmat' => 0,
        ]);

        return view('admin.statistik.edit', compact('statistik'));
    }

    public function statistikUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'santri_aktif' => ['required', 'integer', 'min:0'],
            'asatidzah_bersanad' => ['required', 'integer', 'min:0'],
            'tahun_berkhidmat' => ['required', 'integer', 'min:0'],
        ]);

        $statistik = Statistik::first() ?? new Statistik;
        $statistik->fill($validated)->save();

        return back()->with('success', 'Statistik live counter berhasil diperbarui.');
    }

    // ==================== INFORMASI ====================
    public function informasiIndex()
    {
        $informasis = Informasi::latest()->get();

        return view('admin.informasi.index', compact('informasis'));
    }

    public function informasiStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
        ]);

        Informasi::create($validated);

        return back()->with('success', 'Informasi pengumuman berhasil ditambahkan.');
    }

    public function informasiUpdate(Request $request, int $id): RedirectResponse
    {
        $informasi = Informasi::findOrFail($id);

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
        ]);

        $informasi->update($validated);

        return back()->with('success', 'Informasi pengumuman berhasil diperbarui.');
    }

    public function informasiDestroy(int $id): RedirectResponse
    {
        $informasi = Informasi::findOrFail($id);
        $informasi->delete();

        return back()->with('success', 'Informasi pengumuman berhasil dihapus.');
    }

    // ==================== KONTAK ====================
    public function kontak()
    {
        $kontak = Kontak::first() ?? Kontak::create([
            'alamat' => '',
            'telepon' => '',
            'email' => '',
        ]);

        return view('admin.kontak', compact('kontak'));
    }

    public function kontakUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'alamat' => ['required', 'string'],
            'telepon' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $kontak = Kontak::first() ?? new Kontak;
        $kontak->fill($validated)->save();

        return back()->with('success', 'Kontak resmi institusi berhasil diperbarui.');
    }
}
