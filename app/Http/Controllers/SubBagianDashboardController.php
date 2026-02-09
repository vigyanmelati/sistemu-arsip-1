<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Arsip;
use Illuminate\Support\Facades\Auth;

class SubBagianDashboardController extends Controller
{
    /**
     * Hanya bisa diakses user sub-bagian
     */
    // public function __construct()
    // {
    //     $this->middleware(['auth', 'subbagian']);
    // }

    /**
     * Tampilkan dashboard sub-bagian
     */
    public function index()
    {
        $user = Auth::user();

        // Pastikan user punya sub_bagian_id
        if (!$user->sub_bagian_id) {
            return redirect()->route('home')
                ->with('error', 'Sub Bagian tidak ditemukan.');
        }

        // Query arsip milik sub-bagian
        $arsipQuery = Arsip::where('sub_bagian_id', $user->sub_bagian_id);

        // Jumlah total arsip
        $totalArsip = $arsipQuery->count();

        // Jumlah arsip per status
        $statusOptions = ['AKTIF', 'INAKTIF', 'USUL_MUSNAH', 'PERMANEN', 'MUSNAH'];
        $arsipPerStatus = [];
        foreach ($statusOptions as $status) {
            $arsipPerStatus[$status] = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
                ->where('status_arsip', $status)
                ->count();
        }

        return view('subbagian.dashboard', [
            'totalArsip' => $totalArsip,
            'arsipPerStatus' => $arsipPerStatus,
        ]);
    }
}
