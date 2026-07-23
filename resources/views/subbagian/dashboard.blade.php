@extends('layouts.app')

@section('page-title', 'Dashboard Sub Bagian')
@section('page-subtitle', 'Ringkasan Arsip Sub Bagian Anda')

@section('content')

{{-- ============================================================ --}}
{{-- NOTIFIKASI ARSIP DITOLAK --}}
{{-- ============================================================ --}}
@if(isset($arsipDitolak) && $arsipDitolak > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-start">
                <div class="me-3">
                    <i class="bi bi-exclamation-triangle-fill fs-2 text-danger"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="alert-heading fw-bold mb-2">
                        Ada {{ $arsipDitolak }} Arsip Ditolak!
                    </h5>
                    <p class="mb-2">
                        Terdapat <strong>{{ $arsipDitolak }}</strong> arsip yang ditolak oleh Unit Kearsipan.
                        @if(isset($arsipDitolakBelumDiperbaiki) && $arsipDitolakBelumDiperbaiki > 0)
                            <span class="badge bg-danger">{{ $arsipDitolakBelumDiperbaiki }}</span> di antaranya <strong>belum diperbaiki</strong>.
                        @endif
                    </p>
                    <p class="mb-0">
                        <a href="{{ route('subbagian.riwayat-pemindahan.index') }}?status=DITOLAK"
                           class="btn btn-danger btn-sm">
                            <i class="bi bi-eye me-1"></i> Lihat Arsip Ditolak
                        </a>
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ============================================================ --}}
{{-- STATISTIK CARD --}}
{{-- ============================================================ --}}
<div class="row">

    <!-- Total Arsip -->
    <div class="col-xl-2 col-lg-4 col-md-6 mb-4">
        <div class="card h-100 dashboard-card-clickable"
            onclick="window.location='{{ route('subbagian.arsip.index') }}'">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Arsip</h6>
                        <h2 class="fw-bold text-primary mb-0">{{ $totalArsip }}</h2>
                        <small class="text-muted">Klik untuk melihat seluruh arsip</small>
                    </div>
                    <div class="icon-shape bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-archive text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Arsip Dipindahkan -->
    <div class="col-xl-2 col-lg-4 col-md-6 mb-4">
        <div class="card h-100 dashboard-card-clickable"
            onclick="window.location='{{ route('subbagian.riwayat-pemindahan.index') }}?status=DIPINDAHKAN'">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Arsip Dipindahkan</h6>
                        <h2 class="fw-bold text-success mb-0">{{ $arsipDipindahkan }}</h2>
                        <small class="text-success">
                            {{ $totalArsip > 0 ? number_format(($arsipDipindahkan/$totalArsip)*100, 1) : 0 }}% dari total
                        </small>
                    </div>
                    <div class="icon-shape bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Arsip Diajukan -->
    <div class="col-xl-2 col-lg-4 col-md-6 mb-4">
        <div class="card h-100 dashboard-card-clickable"
            onclick="window.location='{{ route('subbagian.riwayat-pemindahan.index') }}?status=DIAJUKAN'">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Arsip Diajukan</h6>
                        <h2 class="fw-bold text-warning mb-0">{{ $arsipDiajukan }}</h2>
                        <small class="text-warning">
                            {{ $totalArsip > 0 ? number_format(($arsipDiajukan/$totalArsip)*100, 1) : 0 }}% dari total
                        </small>
                    </div>
                    <div class="icon-shape bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-clock text-warning fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Arsip Ditolak -->
    <div class="col-xl-2 col-lg-4 col-md-6 mb-4">
        <div class="card h-100 dashboard-card-clickable {{ isset($arsipDitolak) && $arsipDitolak > 0 ? 'border-danger' : '' }}"
            onclick="window.location='{{ route('subbagian.riwayat-pemindahan.index') }}?status=DITOLAK'">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Arsip Ditolak</h6>
                        <h2 class="fw-bold {{ isset($arsipDitolak) && $arsipDitolak > 0 ? 'text-danger' : 'text-muted' }} mb-0">
                            {{ $arsipDitolak ?? 0 }}
                        </h2>
                    </div>
                    <div class="icon-shape {{ isset($arsipDitolak) && $arsipDitolak > 0 ? 'bg-danger' : 'bg-secondary' }} bg-opacity-10 rounded-circle p-3">
                        <i class="bi {{ isset($arsipDitolak) && $arsipDitolak > 0 ? 'bi-x-circle text-danger' : 'bi-check-circle text-secondary' }} fs-4"></i>
                    </div>
                </div>
                @if(isset($arsipDitolak) && $arsipDitolak > 0)
                    <small class="text-danger">Klik untuk melihat arsip ditolak</small>
                @else
                    <small class="text-muted">Tidak ada arsip ditolak</small>
                @endif
            </div>
        </div>
    </div>

    <!-- Arsip Belum Upload Dokumen -->
    <div class="col-xl-3 col-lg-6 mb-4">
        <div class="card h-100 dashboard-card-clickable {{ ($arsipBelumUploadDokumen ?? 0) > 0 ? 'border-info' : '' }}"
            onclick="window.location='{{ route('subbagian.arsip.index', ['filter' => 'belum_dokumen']) }}'">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Belum Upload Dokumen</h6>
                        <h2 class="fw-bold text-info mb-0">{{ $arsipBelumUploadDokumen ?? 0 }}</h2>
                        <small class="text-info">
                            @if(($arsipBelumUploadDokumen ?? 0) > 0)
                                Klik untuk melihat arsip
                            @else
                                Semua arsip lengkap
                            @endif
                        </small>
                    </div>
                    <div class="icon-shape bg-info bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-file-earmark-text text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ============================================================ --}}
{{-- CHART DISTRIBUSI --}}
{{-- ============================================================ --}}
@if(isset($arsipPerTahun) && $arsipPerTahun->count() > 0)
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i> Distribusi Arsip per Tahun</h5>
            </div>
            <div class="card-body">
                <div class="overflow-auto">
                    <div style="min-width: 700px; height:300px;">
                        <div class="row align-items-end h-100">
                            @php
                                $maxArsip = $arsipPerTahun->max('total') ?? 1;
                            @endphp
                            @foreach($arsipPerTahun as $item)
                            <div class="col text-center">
                                <div class="d-flex justify-content-center mb-2" style="height: 200px; align-items: end;">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="bg-primary rounded-top"
                                             style="width: 25px; height: {{ ($item->total/$maxArsip)*200 }}px;"></div>
                                        <small class="text-muted mt-1">{{ $item->total }}</small>
                                    </div>
                                </div>
                                <small class="text-muted">{{ $item->tahun_arsip }}</small>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ============================================================ --}}
{{-- ARSIP TERBARU --}}
{{-- ============================================================ --}}
@if(isset($arsipTerbaru) && $arsipTerbaru->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i> Arsip Terbaru</h6>
                <a href="{{ route('subbagian.arsip.index') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-arrow-right me-1"></i> Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode</th>
                                <th>Judul Arsip</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($arsipTerbaru as $index => $arsip)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $arsip->kodeKlasifikasi->kode ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($arsip->uraian_arsip, 50) }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'DIPINDAHKAN' => 'success',
                                            'DIAJUKAN' => 'warning',
                                            'DITOLAK' => 'danger',
                                            'BELUM' => 'secondary'
                                        ];
                                        $color = $statusColors[$arsip->status_pindah] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        {{ $arsip->status_pindah ?? 'BELUM' }}
                                    </span>
                                </td>
                                <td>{{ $arsip->created_at ? $arsip->created_at->format('d/m/Y') : '-' }}</td>
                                <td>
                                    <a href="{{ route('subbagian.arsip.show', $arsip->id) }}"
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($arsip->status_pindah == 'DITOLAK')
                                        <a href="{{ route('subbagian.arsip.edit', $arsip->id) }}"
                                           class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<style>
    .icon-shape {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card {
        border-radius: 12px;
        border: 1px solid #e9ecef;
        transition: all 0.2s;
    }
    .card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .alert-danger {
        border-left: 5px solid #dc3545;
    }
    .table th {
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        color: #6c757d;
        border-top: none;
    }
    .table td {
        vertical-align: middle;
        padding: 10px 12px;
    }
    .dashboard-card-clickable {
        cursor: pointer;
        transition: all .25s ease;
    }
    .dashboard-card-clickable:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,.12);
    }
</style>

@endsection