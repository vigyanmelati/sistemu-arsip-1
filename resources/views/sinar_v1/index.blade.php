@extends('layouts.app')

@section('title', 'SINAR V1 — Dokumen Historis')
@section('page-title', 'SINAR V1')
@section('page-subtitle', 'Dokumen historis hasil migrasi dari aplikasi SINAR versi 1')

@section('content')
@if(in_array(strtolower((string) auth()->user()->role), ['admin','super_admin','tu']))
<div class="d-flex justify-content-end mb-3"><a href="{{ route('sinar-v1.import') }}" class="btn btn-primary"><i class="bi bi-database-up"></i> Import Data SINAR V1</a></div>
@endif
<div class="alert alert-info border-0 shadow-sm">
    <div class="d-flex gap-3">
        <i class="bi bi-info-circle-fill fs-4"></i>
        <div><strong>Data historis SINAR V1</strong><br>
            Keberadaan data di menu ini tidak otomatis menyatakan bahwa hardcopy telah ditemukan atau terdaftar sebagai Arsip V2.
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-lg-4"><label class="form-label">Pencarian</label><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Nomor, perihal, atau instansi"></div>
            <div class="col-lg-3"><label class="form-label">Kategori</label><select name="category" class="form-select"><option value="">Semua kategori</option>@foreach(\App\Models\SinarV1Document::CATEGORIES as $id => $name)<option value="{{ $id }}" @selected((string) request('category') === (string) $id)>{{ $name }}</option>@endforeach</select></div>
            <div class="col-lg-2"><label class="form-label">Tahun</label><select name="year" class="form-select"><option value="">Semua</option>@foreach($years as $year)<option @selected((string) request('year') === (string) $year)>{{ $year }}</option>@endforeach</select></div>
            <div class="col-lg-3 d-flex gap-2"><button class="btn btn-primary"><i class="bi bi-search"></i> Cari</button><a href="{{ route('sinar-v1.index') }}" class="btn btn-outline-secondary">Reset</a></div>
            <div class="col-lg-3"><label class="form-label">Status hardcopy</label><select name="hardcopy" class="form-select"><option value="">Semua</option>@foreach(\App\Models\SinarV1Document::HARDCOPY_STATUSES as $key => $label)<option value="{{ $key }}" @selected(request('hardcopy') === $key)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-lg-3"><label class="form-label">Status integrasi</label><select name="integration" class="form-select"><option value="">Semua</option>@foreach(\App\Models\SinarV1Document::INTEGRATION_STATUSES as $key => $label)<option value="{{ $key }}" @selected(request('integration') === $key)>{{ $label }}</option>@endforeach</select></div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between"><strong>Daftar Dokumen</strong><span class="badge bg-secondary">{{ $documents->total() }} data</span></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Kategori</th><th>Nomor/Tanggal</th><th>Instansi dan Perihal</th><th>Subbagian</th><th>File</th><th>Hardcopy</th><th></th></tr></thead>
            <tbody>
            @forelse($documents as $document)
                <tr>
                    <td><span class="badge {{ $document->isOutgoingLetter() ? 'bg-warning text-dark' : 'bg-primary' }}">{{ $document->legacy_category_name }}</span></td>
                    <td><strong>{{ $document->nomor_dokumen ?: '—' }}</strong><br><small class="text-muted">{{ $document->tanggal_dokumen?->format('d-m-Y') ?: 'Tanggal tidak tersedia' }}</small></td>
                    <td>{{ $document->instansi_satker ?: '—' }}<br><small class="text-muted">{{ \Illuminate\Support\Str::limit($document->perihal, 90) ?: '—' }}</small></td>
                    <td>{{ $document->subBagian?->nama_sub_bagian ?: $document->legacy_bagian_name ?: 'Belum dipetakan' }}</td>
                    <td><span class="badge {{ $document->status_file === 'TERSEDIA' ? 'bg-success' : ($document->status_file === 'HILANG' ? 'bg-danger' : 'bg-secondary') }}">{{ str_replace('_', ' ', $document->status_file) }}</span></td>
                    <td><span class="badge bg-light text-dark border">{{ \App\Models\SinarV1Document::HARDCOPY_STATUSES[$document->status_hardcopy] ?? $document->status_hardcopy }}</span></td>
                    <td><a href="{{ route('sinar-v1.show', $document) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">Belum ada dokumen SINAR V1 yang sesuai.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($documents->hasPages())<div class="card-footer">{{ $documents->links() }}</div>@endif
</div>
@endsection
