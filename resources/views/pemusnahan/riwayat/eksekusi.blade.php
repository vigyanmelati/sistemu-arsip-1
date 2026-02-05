@extends('layouts.app')

@section('title','Eksekusi Pemusnahan Arsip')

@section('content')
<div class="container-fluid">

<h4 class="mb-3">🔥 Eksekusi Pemusnahan Arsip</h4>

<form method="POST"
      action="{{ route('pemusnahan.eksekusi.simpan',$pemusnahan->id) }}"
      enctype="multipart/form-data">
@csrf

<div class="card">
<div class="card-body">

@php
$fields = [
 'nota_dinas' => 'Nota Dinas',
 'surat_undangan' => 'Surat Undangan',
 'berita_acara_penilaian' => 'Berita Acara Penilaian Arsip',
 'surat_pertimbangan' => 'Surat Pertimbangan Penilaian Arsip',
 'notulen_rapat' => 'Notulen Rapat',
 'surat_permohonan_anri' => 'Surat Permohonan Izin ANRI',
 'surat_persetujuan_anri' => 'Surat Persetujuan ANRI',
 'surat_permohonan_kpu_ri' => 'Surat Permohonan KPU RI',
 'surat_persetujuan_kpu_ri' => 'Surat Persetujuan KPU RI',
 'surat_undangan_pemusnahan' => 'Surat Undangan Pemusnahan',
 'notulen_pemusnahan' => 'Notulen Pemusnahan',
 'berita_acara_pemusnahan' => 'Berita Acara Pemusnahan'
];
@endphp

@foreach($fields as $name => $label)
<div class="mb-3">
    <label class="form-label">{{ $label }}</label>
    <input type="file" name="{{ $name }}" class="form-control" required>
</div>
@endforeach

</div>
<div class="card-footer text-end">
<button class="btn btn-danger"
onclick="return confirm('Yakin arsip DIMUSNAHKAN secara fisik?')">
🔥 Tetapkan Pemusnahan
</button>
</div>
</div>

</form>
</div>
@endsection
