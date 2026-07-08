<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use Illuminate\Http\Request;

class LintasUnitController extends Controller
{
    public function index()
    {
        return view('lintas-unit.index');
    }

    public function daftar(Request $request, $unit)
    {
        // UNIT KEARSIPAN
        if ($unit == 'unit-kearsipan') {

        $query = Arsip::whereIn('status_pindah', [
            'LANGSUNG',
            'DIPINDAHKAN'
        ]);

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

            $arsips = Arsip::where('sub_bagian_id', 1)
                ->where('status_pindah', 'BELUM')
                ->latest()
                ->paginate(20);

            $title = 'Daftar Arsip Sub Bagian Umum dan Logistik';
        }

        // SUB BAGIAN PARTISIPASI
        elseif ($unit == 'subbag-partisipasi') {

            $arsips = Arsip::where('sub_bagian_id', 2)
                ->where('status_pindah', 'BELUM')
                ->latest()
                ->paginate(20);

            $title = 'Daftar Arsip Sub Bagian Partisipasi, Hubungan Masyarakat dan SDM';
        }

        // SUB BAGIAN KEUANGAN
        elseif ($unit == 'subbag-keuangan') {

            $arsips = Arsip::where('sub_bagian_id', 3)
                ->where('status_pindah', 'BELUM')
                ->latest()
                ->paginate(20);

            $title = 'Daftar Arsip Sub Bagian Keuangan';
        }

        // SUB BAGIAN PERENCANAAN
        elseif ($unit == 'subbag-perencanaan') {

            $arsips = Arsip::where('sub_bagian_id', 4)
                ->where('status_pindah', 'BELUM')
                ->latest()
                ->paginate(20);

            $title = 'Daftar Arsip Sub Bagian Perencanaan, Data, dan Informasi';
        }

        // SUB BAGIAN TEKNIS
        elseif ($unit == 'subbag-teknis') {

            $arsips = Arsip::where('sub_bagian_id', 5)
                ->where('status_pindah', 'BELUM')
                ->latest()
                ->paginate(20);

            $title = 'Daftar Arsip Sub Bagian Teknis Penyelenggaraan Pemilu';
        }

        // SUB BAGIAN HUKUM
        elseif ($unit == 'subbag-hukum') {

            $arsips = Arsip::where('sub_bagian_id', 6)
                ->where('status_pindah', 'BELUM')
                ->latest()
                ->paginate(20);

            $title = 'Daftar Arsip Sub Bagian Hukum';
        }

        else {
            abort(404);
        }

        return view('lintas-unit.daftar', compact('arsips', 'title'));
    }
}