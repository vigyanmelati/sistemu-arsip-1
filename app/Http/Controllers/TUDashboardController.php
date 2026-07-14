<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use Carbon\Carbon;

class TuDashboardController extends Controller
{
    public function index()
    {
        return view('tu.dashboard', [
            'totalSuratMasuk' => SuratMasuk::count(),
            'suratBulanIni' => SuratMasuk::whereMonth('tanggal_dokumen', Carbon::now()->month)
                                        ->whereYear('tanggal_dokumen', Carbon::now()->year)
                                        ->count(),
            'suratHariIni' => SuratMasuk::whereDate('tanggal_dokumen', Carbon::today())
                                        ->count(),
        ]);
    }
}