<?php

namespace App\Http\Controllers\WaliMurid;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaliMuridDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $children = Santri::where('wali_murid_id', auth()->id())
            ->with([
                'setoranHafalan' => fn ($q) => $q->latest('tanggal')->latest('id'),
                'murojaah' => fn ($q) => $q->latest('tanggal')->latest('id'),
            ])
            ->get();

        return view('wali.dashboard', compact('children'));
    }
}
