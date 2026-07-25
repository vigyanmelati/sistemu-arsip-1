@extends('layouts.app')

@section('title', 'Detail Dokumen SINAR V1')
@section('page-title', 'Detail Dokumen SINAR V1')
@section('page-subtitle', 'Data historis — bukan bukti keberadaan hardcopy')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="d-flex justify-content-between mb-3"><a href="{{ route('sinar-v1.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    @if($document->file_path && $document->status_file === 'TERSEDIA')<a href="{{ route('sinar-v1.download', $document) }}" class="btn btn-primary"><i class="bi bi-download"></i> Unduh Lampiran</a>@endif
</div>

<div class="row g-4">
<div class="col-lg-8"><div class="card shadow-sm"><div class="card-header"><strong>{{ $document->legacy_category_name }}</strong> <span class="badge bg-secondary">Legacy ID {{ $document->legacy_id }}</span></div>
<div class="card-body"><dl class="row mb-0">
    <dt class="col-sm-4">Nomor dokumen</dt><dd class="col-sm-8">{{ $document->nomor_dokumen ?: '—' }}</dd>
    <dt class="col-sm-4">Nomor agenda</dt><dd class="col-sm-8">{{ $document->nomor_agenda ?: '—' }}</dd>
    <dt class="col-sm-4">Tanggal dokumen</dt><dd class="col-sm-8">{{ $document->tanggal_dokumen?->format('d-m-Y') ?: '—' }}</dd>
    <dt class="col-sm-4">Instansi/Satker</dt><dd class="col-sm-8">{{ $document->instansi_satker ?: '—' }}</dd>
    <dt class="col-sm-4">Kepada</dt><dd class="col-sm-8">{{ $document->kepada ?: '—' }}</dd>
    <dt class="col-sm-4">Perihal</dt><dd class="col-sm-8">{{ $document->perihal ?: '—' }}</dd>
    <dt class="col-sm-4">Catatan lama</dt><dd class="col-sm-8">{{ $document->catatan ?: '—' }}</dd>
    <dt class="col-sm-4">Bagian SINAR V1</dt><dd class="col-sm-8">{{ $document->legacy_bagian_name ?: '—' }}</dd>
    <dt class="col-sm-4">Subbagian V2</dt><dd class="col-sm-8">{{ $document->subBagian?->nama_sub_bagian ?: 'Belum dipetakan' }}</dd>
</dl></div></div></div>

<div class="col-lg-4"><div class="card shadow-sm"><div class="card-header"><strong>Verifikasi</strong></div><div class="card-body">
@if(in_array(strtolower((string) auth()->user()->role), ['admin','super_admin','tu']))
<form method="POST" action="{{ route('sinar-v1.verification.update', $document) }}">@csrf @method('PUT')
    <div class="mb-3"><label class="form-label">Pemetaan subbagian</label><select name="sub_bagian_id" class="form-select"><option value="">Belum dipetakan</option>@foreach($subBagians as $sub)<option value="{{ $sub->id }}" @selected(old('sub_bagian_id', $document->sub_bagian_id) == $sub->id)>{{ $sub->nama_sub_bagian }}</option>@endforeach</select></div>
    <div class="mb-3"><label class="form-label">Status hardcopy</label><select name="status_hardcopy" class="form-select">@foreach(\App\Models\SinarV1Document::HARDCOPY_STATUSES as $key=>$label)<option value="{{ $key }}" @selected(old('status_hardcopy',$document->status_hardcopy)===$key)>{{ $label }}</option>@endforeach</select></div>
    <div class="mb-3"><label class="form-label">Status integrasi</label><select name="status_integrasi" class="form-select">@foreach(\App\Models\SinarV1Document::INTEGRATION_STATUSES as $key=>$label)<option value="{{ $key }}" @selected(old('status_integrasi',$document->status_integrasi)===$key)>{{ $label }}</option>@endforeach</select></div>
    <div class="mb-3"><label class="form-label">Lokasi hardcopy sementara</label><input name="lokasi_hardcopy" value="{{ old('lokasi_hardcopy',$document->lokasi_hardcopy) }}" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Catatan verifikasi</label><textarea name="catatan_verifikasi" class="form-control" rows="3">{{ old('catatan_verifikasi',$document->catatan_verifikasi) }}</textarea></div>
    <button class="btn btn-success w-100">Simpan Verifikasi</button>
</form>
@else
<p><strong>Hardcopy:</strong><br>{{ \App\Models\SinarV1Document::HARDCOPY_STATUSES[$document->status_hardcopy] ?? $document->status_hardcopy }}</p>
<p><strong>Integrasi:</strong><br>{{ \App\Models\SinarV1Document::INTEGRATION_STATUSES[$document->status_integrasi] ?? $document->status_integrasi }}</p>
@endif

@if(in_array(strtolower((string) auth()->user()->role), ['admin','super_admin','tu']) && !$document->arsip_id)
<hr><form method="POST" action="{{ route('sinar-v1.archive.prepare', $document) }}">@csrf<button class="btn btn-outline-primary w-100" @disabled(!in_array($document->status_hardcopy,['DITEMUKAN','HANYA_DIGITAL']))><i class="bi bi-folder-plus"></i> Daftarkan sebagai Arsip V2</button></form>
<small class="text-muted d-block mt-2">Aktif setelah hardcopy ditemukan atau dokumen dinyatakan hanya digital.</small>
@elseif($document->arsip_id)<hr><a href="{{ route('arsip.show',$document->arsip_id) }}" class="btn btn-primary w-100">Lihat Arsip V2</a>@endif
</div></div></div>
</div>
@endsection
