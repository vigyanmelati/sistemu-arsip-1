<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Arsip;
use App\Models\KodeKlasifikasi;
use App\Models\SubBagian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ArsipImport;
use App\Exports\ArsipExport;
use Carbon\Carbon;

class SubBagianArsipController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Arsip::with(['kodeKlasifikasi', 'subBagian'])
            ->where('sub_bagian_id', $user->sub_bagian_id); // scope sub bagian

        $tahunOptions = Arsip::select('tahun_arsip')
            ->distinct()
            ->orderBy('tahun_arsip', 'desc')
            ->pluck('tahun_arsip');

        $subBagianOptions = SubBagian::select('id', 'nama_sub_bagian as nama_sub_bagian')
            ->orderBy('nama_sub_bagian')
            ->get();

        // Filter & search sama seperti ArsipController
        if ($request->has('status_arsip') && $request->status_arsip != '') {
            $query->where('status_arsip', $request->status_arsip);
        }
        if ($request->has('tahun_arsip') && $request->tahun_arsip != '') {
            $query->where('tahun_arsip', $request->tahun_arsip);
        }
        if ($request->has('kode_klasifikasi_id') && $request->kode_klasifikasi_id != '') {
            $query->where('kode_klasifikasi_id', $request->kode_klasifikasi_id);
        }
        if ($request->has('keterangan') && $request->keterangan != '') {
            $query->where('keterangan', $request->keterangan);
        }
        if ($request->has('keterangan_jra') && $request->keterangan_jra != '') {
            $query->where('keterangan_jra', $request->keterangan_jra);
        }
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('uraian_arsip', 'like', "%{$request->search}%")
                  ->orWhereHas('kodeKlasifikasi', function($sub) use ($request){
                      $sub->where('kode','like',"%{$request->search}%")
                          ->orWhere('uraian','like',"%{$request->search}%");
                  });
            });
        }

        $arsips = $query->orderBy('id','desc')->paginate(15);

        // Filter dropdown options
        $kodeKlasifikasiOptions = KodeKlasifikasi::orderBy('kode')->get();
        $statusOptions = ['AKTIF'=>'Aktif','INAKTIF'=>'Inaktif','USUL_MUSNAH'=>'Usul Musnah','MUSNAH'=>'Musnah','PERMANEN'=>'Permanen'];
        $kondisiOptions = ['BAIK'=>'Baik','RUSAK'=>'Rusak','HILANG'=>'Hilang'];
        $keteranganJraOptions = ['MUSNAH'=>'Musnah','PERMANEN'=>'Permanen'];

        return view('subbagian.arsip.index', compact(
            'arsips','kodeKlasifikasiOptions','statusOptions','kondisiOptions','keteranganJraOptions', 'tahunOptions','subBagianOptions'
        ));
    }

    public function create()
    {
        $kodeKlasifikasiOptions = KodeKlasifikasi::orderBy('kode')->get();
        $subBagianOptions = SubBagian::orderBy('nama_sub_bagian')->get();
        $defaultValues = [
            'status_arsip' => 'AKTIF',
            'jumlah_berkas' => 1,
            'satuan_arsip' => 'LEMBAR',
            'tahun_arsip' => date('Y'),
            'tanggal_arsip' => date('Y-m-d'),
            'keterangan_jra' => 'MUSNAH',
        ];
        return view('subbagian.arsip.create', compact('kodeKlasifikasiOptions','defaultValues','subBagianOptions'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'kode_klasifikasi_id'=>'required|exists:kode_klasifikasis,id',
            'uraian_arsip'=>'required|string|max:500',
            'tahun_arsip'=>'required|integer|min:2000|max:'.(date('Y')+1),
            'tanggal_arsip'=>'required|date',
            'jumlah_berkas'=>'required|integer|min:1',
            'satuan_arsip'=>'required|in:BENDEL,LEMBAR',
            // 'aktif_tahun'=>'nullable|string|max:100',
            // 'inaktif_tahun'=>'nullable|string|max:100',
            // 'tanggal_referensi'=>'nullable|date',
            // 'keterangan_jra'=>'nullable|in:PERMANEN,MUSNAH',
            'nomor_rak'=>'nullable|string|max:50',
            'nomor_box'=>'nullable|string|max:50',
            'nomor_sampul'=>'nullable|string|max:100',
            'tingkat_perkembangan'=>'nullable|in:ASLI,COPY,SALINAN',
            'keterangan'=>'nullable|in:BAIK,RUSAK,HILANG',
            'media_arsip'=>'nullable|string|max:255',
            'file_dokumen'=>'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $validated['sub_bagian_id'] = $user->sub_bagian_id;
        $validated['created_by'] = $user->id;
        $validated['tanggal_masuk'] = now()->format('Y-m-d');

        if($request->hasFile('file_dokumen')){
            $file=$request->file('file_dokumen');
            $fileName = time().'_'.$file->getClientOriginalName();
            $file->storeAs('arsip',$fileName,'public');
            $validated['file_dokumen'] = $fileName;
        }

        // $perhitungan = (new \App\Http\Controllers\ArsipController)->hitungRetensi(
        //     $validated['aktif_tahun'] ?? null,
        //     $validated['inaktif_tahun'] ?? null,
        //     $validated['keterangan_jra'],
        //     $validated['tanggal_arsip'],
        //     $validated['tanggal_referensi'] ?? null
        // );

        // $validated['aktif_sampai']=$perhitungan['aktif_sampai'];
        // $validated['inaktif_sampai']=$perhitungan['inaktif_sampai'];
        // $validated['status_arsip']=$perhitungan['status_arsip'];

        Arsip::create($validated);

        return redirect()->route('subbagian.arsip.index')->with('success','Arsip berhasil ditambahkan.');
    }

    public function edit(Arsip $arsip)
    {
        $user = Auth::user();
        if($arsip->sub_bagian_id != $user->sub_bagian_id) abort(403);

        $kodeKlasifikasiOptions = KodeKlasifikasi::orderBy('kode')->get();
        $subBagianOptions = SubBagian::orderBy('nama_sub_bagian')->get(); // tambahkan ini
        return view('subbagian.arsip.edit', compact('arsip','kodeKlasifikasiOptions','subBagianOptions'));
    }

    public function show(Request $request, Arsip $arsip)
    {
        return view('subbagian.arsip.show', [
            'arsip' => $arsip,
            'returnUrl' => $request->get('return')
        ]);
    }

    public function update(Request $request, Arsip $arsip)
    {
        $user = Auth::user();
        if($arsip->sub_bagian_id != $user->sub_bagian_id) abort(403);

        $validated = $request->validate([
            'kode_klasifikasi_id'=>'required|exists:kode_klasifikasis,id',
            'uraian_arsip'=>'required|string|max:500',
            'tahun_arsip'=>'required|integer|min:2000|max:'.(date('Y')+1),
            'tanggal_arsip'=>'required|date',
            'jumlah_berkas'=>'required|integer|min:1',
            'satuan_arsip'=>'required|in:BENDEL,LEMBAR',
            // 'aktif_tahun'=>'required|string|max:100',
            // 'inaktif_tahun'=>'required|string|max:100',
            // 'tanggal_referensi'=>'nullable|date',
            // 'keterangan_jra'=>'required|in:PERMANEN,MUSNAH',
            'nomor_rak'=>'nullable|string|max:50',
            'nomor_box'=>'nullable|string|max:50',
            'nomor_sampul'=>'nullable|string|max:100',
            'tingkat_perkembangan'=>'nullable|in:ASLI,COPY,SALINAN',
            'keterangan'=>'nullable|in:BAIK,RUSAK,HILANG',
            'media_arsip'=>'nullable|string|max:255',
            'file_dokumen'=>'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'hapus_file'=>'nullable|in:0,1'
        ]);

        if(($request->hapus_file??'0')=='1' && $arsip->file_dokumen){
            Storage::disk('public')->delete('arsip/'.$arsip->file_dokumen);
            $validated['file_dokumen']=null;
        }

        if($request->hasFile('file_dokumen')){
            if($arsip->file_dokumen) Storage::disk('public')->delete('arsip/'.$arsip->file_dokumen);
            $file=$request->file('file_dokumen');
            $fileName = time().'_'.$file->getClientOriginalName();
            $file->storeAs('arsip',$fileName,'public');
            $validated['file_dokumen']=$fileName;
        }

        // $perhitungan = (new \App\Http\Controllers\ArsipController)->hitungRetensi(
        //     $validated['aktif_tahun'] ?? null,
        //     $validated['inaktif_tahun'] ?? null,
        //     $validated['keterangan_jra'],
        //     $validated['tanggal_arsip'],
        //     $validated['tanggal_referensi'] ?? null
        // );
        // $validated['aktif_sampai']=$perhitungan['aktif_sampai'];
        // $validated['inaktif_sampai']=$perhitungan['inaktif_sampai'];
        // $validated['status_arsip']=$perhitungan['status_arsip'];

        $arsip->update($validated);

        return redirect()->route('subbagian.arsip.index')->with('success','Arsip berhasil diperbarui.');
    }

    public function destroy(Arsip $arsip)
    {
        $user = Auth::user();
        if($arsip->sub_bagian_id != $user->sub_bagian_id) abort(403);

        if($arsip->file_dokumen) Storage::disk('public')->delete('arsip/'.$arsip->file_dokumen);
        $arsip->delete();
        return redirect()->route('subbagian.arsip.index')->with('success','Arsip berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate(['file_excel'=>'required|file|mimes:xlsx,xls']);
        $user = Auth::user();
        Excel::import(new ArsipImport($user->sub_bagian_id, $user->id), $request->file('file_excel'));
        return redirect()->route('subbagian.arsip.index')->with('success','Data arsip berhasil diimport.');
    }

    public function export(Request $request)
    {
        $columns = $request->input('columns',[]);
        if(count($columns)==0) return back()->with('error','Pilih minimal satu kolom.');

        return Excel::download(
            new ArsipExport($request,$columns,Auth::user()->sub_bagian_id),
            'arsip_subbagian_'.date('Y-m-d-H-i-s').'.xlsx'
        );
    }

    public function ajukanPindah(Request $request, Arsip $arsip)
    {
        $user = Auth::user();
        if($arsip->sub_bagian_id != $user->sub_bagian_id) abort(403);

        $request->validate([
            'file_berita_acara'=>'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $file = $request->file('file_berita_acara');
        $fileName = time().'_'.$file->getClientOriginalName();
        $file->storeAs('arsip',$fileName,'public');
        $arsip->file_berita_acara = $fileName;
        $arsip->status_pindah='DIAJUKAN';
        $arsip->save();

        return back()->with('success','Arsip berhasil diajukan pemindahannya.');
    }
}
