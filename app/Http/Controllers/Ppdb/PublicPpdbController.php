<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Carousel;
use App\Models\Informasi;
use App\Models\Keunggulan;
use App\Models\Kontak;
use App\Models\Statistik;
use Illuminate\View\View;

class PublicPpdbController extends Controller
{
    public function home(): View
    {
        $carousels = Carousel::orderBy('order')->get();
        $keunggulans = Keunggulan::orderBy('order')->get();
        $statistik = Statistik::first();
        $kontak = Kontak::first();

        return view('public.home', compact('carousels', 'keunggulans', 'statistik', 'kontak'));
    }

    public function informasi(): View
    {
        $informasis = Informasi::latest()->paginate(10);

        return view('public.informasi', compact('informasis'));
    }

    public function contact(): View
    {
        $kontak = Kontak::first();

        return view('public.contact', compact('kontak'));
    }
}
