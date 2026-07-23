<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LintasUnitController extends Controller
{
    public function index()
    {
        return view('lintas-unit.index');
    }

    public function daftar(Request $request, $unit)
    {
        $user = Auth::user();

        // UNIT KEARSIPAN
        if ($unit == 'unit-kearsipan') {

            $query = Arsip::whereIn('status_pindah', [
                'LANGSUNG',
                'DIPINDAHKAN'
            ]);

            $query = $this->filterRahasia($query, $user);

            if ($request->filled('search')) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('uraian_arsip', 'like', "%{$search}%")
                        ->orWhere('tahun_arsip', 'like', "%{$search}%")
                        ->orWhereHas('kodeKlasifikasi', function ($k) use ($search) {
                            $k->where('kode', 'like', "%{$search}%");
                        });
                });
            }

            $arsips = $query->latest()->paginate(20)->withQueryString();
            $title = 'Daftar Arsip Unit Kearsipan';
        }

        // SUB BAGIAN UMUM DAN LOGISTIK
        elseif ($unit == 'subbag-umum-logistik') {

            $query = Arsip::where('sub_bagian_id', 1)
                ->where('status_pindah', 'BELUM');

            $query = $this->filterRahasia($query, $user);

            $arsips = $query->latest()->paginate(20);

            $title = 'Daftar Arsip Sub Bagian Umum dan Logistik';
        }

        // SUB BAGIAN PARTISIPASI
        elseif ($unit == 'subbag-partisipasi') {

            $query = Arsip::where('sub_bagian_id', 2)
                ->where('status_pindah', 'BELUM');

            $query = $this->filterRahasia($query, $user);

            $arsips = $query->latest()->paginate(20);

            $title = 'Daftar Arsip Sub Bagian Partisipasi, Hubungan Masyarakat dan SDM';
        }

        // SUB BAGIAN KEUANGAN
        elseif ($unit == 'subbag-keuangan') {

            $query = Arsip::where('sub_bagian_id', 3)
                ->where('status_pindah', 'BELUM');

            $query = $this->filterRahasia($query, $user);

            $arsips = $query->latest()->paginate(20);

            $title = 'Daftar Arsip Sub Bagian Keuangan';
        }

        // SUB BAGIAN PERENCANAAN
        elseif ($unit == 'subbag-perencanaan') {

            $query = Arsip::where('sub_bagian_id', 7)
                ->where('status_pindah', 'BELUM');

            $query = $this->filterRahasia($query, $user);

            $arsips = $query->latest()->paginate(20);

            $title = 'Daftar Arsip Sub Bagian Perencanaan, Data, dan Informasi';
        }

        // SUB BAGIAN TEKNIS
        elseif ($unit == 'subbag-teknis') {

            $query = Arsip::where('sub_bagian_id', 5)
                ->where('status_pindah', 'BELUM');

            $query = $this->filterRahasia($query, $user);

            $arsips = $query->latest()->paginate(20);

            $title = 'Daftar Arsip Sub Bagian Teknis Penyelenggaraan Pemilu';
        }

        // SUB BAGIAN HUKUM
        elseif ($unit == 'subbag-hukum') {

            $query = Arsip::where('sub_bagian_id', 6)
                ->where('status_pindah', 'BELUM');

            $query = $this->filterRahasia($query, $user);

            $arsips = $query->latest()->paginate(20);

            $title = 'Daftar Arsip Sub Bagian Hukum';
        }

        else {
            abort(404);
        }

        return view('lintas-unit.daftar', compact('arsips', 'title'));
    }

    private function filterRahasia($query, $user)
    {
        // Jika role user biasa, arsip rahasia tidak boleh dilihat
        // kecuali arsip yang dibuat oleh dirinya sendiri.
        if ($user->role == 'user') {
            $query->where(function ($q) use ($user) {
                $q->where('klasifikasi_keamanan', '!=', 'Rahasia')
                    ->orWhere('created_by', $user->id);
            });
        }

        return $query;
    }
}