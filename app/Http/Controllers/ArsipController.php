<?php

namespace App\Http\Controllers;

use App\Models\Arsip;

class ArsipController extends Controller
{
    public function index()
    {
        $arsips = Arsip::orderBy('tahun_arsip', 'desc')->get();

        return view('arsip.index', compact('arsips'));
    }
}
