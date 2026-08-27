@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Sistem Informasi Arsip KPU Bali')

@section('content')

<style>
    .stat-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .stat-card-link:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
        color: inherit;
    }
    .stat-card-link .card {
        cursor: pointer;
    }
</style>
@php
    $checker = app(\App\Services\DiskSpaceChecker::class);
@endphp

@foreach($diskUsages as $diskUsage)
    @php
        $freeFormatted = $checker->formatBytes($diskUsage['free_bytes']);
        $usedFormatted = $checker->formatBytes($diskUsage['used_bytes']);
        $totalFormatted = $checker->formatBytes($diskUsage['total_bytes']);

        if ($diskUsage['is_critical']) {
            $bgColor = '#f8d7da'; $borderColor = '#f5c2c7'; $textColor = '#842029';
            $icon = '⚠️'; $label = 'KRITIS - Segera Hubungi Admin IT';
        } elseif ($diskUsage['is_warning']) {
            $bgColor = '#fff3cd'; $borderColor = '#ffecb5'; $textColor = '#664d03';
            $icon = '⚠️'; $label = 'PERINGATAN - Kapasitas Mulai Menipis';
        } else {
            $bgColor = '#d1e7dd'; $borderColor = '#badbcc'; $textColor = '#0f5132';
            $icon = '✅'; $label = 'AMAN';
        }
    @endphp

    <div class="alert" role="alert" style="background-color:{{ $bgColor }}; border-color:{{ $borderColor }}; color:{{ $textColor }}; margin-bottom:10px;">
        <strong>{{ $icon }} Penyimpanan Server ({{ $diskUsage['path'] }}) — {{ $label }}</strong><br>
        Kapasitas Terpakai: {{ $usedFormatted }} dari {{ $totalFormatted }} ({{ $diskUsage['percent_used'] }}%)<br>
        <strong>Sisa Ruang Penyimpanan: {{ $freeFormatted }}</strong>
    </div>
@endforeach

<!-- <div class="alert" role="alert" style="background-color:{{ $bgColor }}; border-color:{{ $borderColor }}; color:{{ $textColor }};">
    <strong>{{ $icon }} Status Penyimpanan Server: {{ $label }}</strong><br>
    Path: <code>{{ $diskUsage['path'] }}</code><br>
    Terpakai: {{ $usedFormatted }} / {{ $totalFormatted }} ({{ $diskUsage['percent_used'] }}%)<br>
    <strong>Sisa Penyimpanan: {{ $freeFormatted }}</strong>
</div> -->
<div class="row">
    
    <!-- Total Arsip -->
    <div class="col-xl-3 col-lg-6 mb-4">
        <a href="{{ route('arsip.index') }}" class="stat-card-link">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Arsip</h6>
                            <h2 class="fw-bold text-primary mb-0">{{ $totalArsip }}</h2>
                            <small class="text-muted">Seluruh data arsip</small>
                        </div>
                        <div class="icon-shape bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-archive text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Arsip Aktif -->
    <div class="col-xl-3 col-lg-6 mb-4">
        <a href="{{ route('arsip.index', ['status_arsip' => 'AKTIF']) }}" class="stat-card-link">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Arsip Aktif</h6>
                            <h2 class="fw-bold text-success mb-0">{{ $arsipAktif }}</h2>
                            <small class="text-success">
                                {{ $totalArsip > 0 ? number_format(($arsipAktif/$totalArsip)*100, 1) : 0 }}% dari total
                            </small>
                        </div>
                        <div class="icon-shape bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-folder-check text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Arsip Inaktif -->
    <div class="col-xl-3 col-lg-6 mb-4">
        <a href="{{ route('arsip.index', ['status_arsip' => 'INAKTIF']) }}" class="stat-card-link">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Arsip Inaktif</h6>
                            <h2 class="fw-bold text-warning mb-0">{{ $arsipInaktif }}</h2>
                            <small class="text-warning">
                                {{ $totalArsip > 0 ? number_format(($arsipInaktif/$totalArsip)*100, 1) : 0 }}% dari total
                            </small>
                        </div>
                        <div class="icon-shape bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-folder-symlink text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Arsip Habis Retensi -->
    <div class="col-xl-3 col-lg-6 mb-4">
        <a href="{{ route('arsip.index', ['status_arsip' => 'HABIS_RETENSI']) }}" class="stat-card-link">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Arsip Habis Retensi</h6>
                            <h2 class="fw-bold text-danger mb-0">{{ $arsipUsulMusnah }}</h2>
                            <small class="text-danger">
                                {{ $totalArsip > 0 ? number_format(($arsipUsulMusnah/$totalArsip)*100, 1) : 0 }}% dari total
                            </small>
                        </div>
                        <div class="icon-shape bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Arsip Musnah -->
    <div class="col-xl-3 col-lg-6 mb-4">
        <a href="{{ route('arsip.index', ['status_arsip' => 'MUSNAH']) }}" class="stat-card-link">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Arsip Musnah</h6>
                            <h2 class="fw-bold text-dark mb-0">{{ $arsipMusnah }}</h2>
                            <small class="text-dark">
                                {{ $totalArsip > 0 ? number_format(($arsipMusnah/$totalArsip)*100, 1) : 0 }}% dari total
                            </small>
                        </div>
                        <div class="icon-shape bg-dark bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-trash-fill text-dark fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Arsip Permanen -->
    <div class="col-xl-3 col-lg-6 mb-4">
        <a href="{{ route('arsip.index', ['status_arsip' => 'PERMANEN']) }}" class="stat-card-link">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Arsip Permanen</h6>
                            <h2 class="fw-bold text-info mb-0">{{ $arsipPermanen }}</h2>
                            <small class="text-info">
                                {{ $totalArsip > 0 ? number_format(($arsipPermanen/$totalArsip)*100, 1) : 0 }}% dari total
                            </small>
                        </div>
                        <div class="icon-shape bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-archive-fill text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Arsip Belum Ada File (file_dokumen kosong & link_foto kosong) -->
    <div class="col-xl-3 col-lg-6 mb-4">
        <a href="{{ route('arsip.index', ['filter' => 'belum_file']) }}" class="stat-card-link">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Arsip Belum Ada File</h6>
                            <h2 class="fw-bold text-secondary mb-0">{{ $arsipBelumFile }}</h2>
                            <small class="text-secondary">
                                {{ $totalArsip > 0 ? number_format(($arsipBelumFile/$totalArsip)*100, 1) : 0 }}% dari total
                            </small>
                        </div>
                        <div class="icon-shape bg-secondary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-file-earmark-excel text-secondary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row">
    <!-- Chart Distribusi -->
    <div class="col-lg-16 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i> Distribusi Arsip per Tahun</h5>
            </div>
            <div class="card-body">
                @if($arsipPerTahun->count() > 0)
                <div class="overflow-auto">
                    <div style="min-width: 700px; height:300px;">
                        <!-- Simple Bar Chart dengan Bootstrap -->
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
                                <small class="text-muted">{{ $item->tahun }}</small>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-bar-chart text-muted fa-3x mb-3"></i>
                    <p class="text-muted">Belum ada data untuk ditampilkan</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Arsip Terbaru -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Arsip Terbaru</h5>
                <a href="{{ route('arsip.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Tambah Arsip
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kode Klasifikasi</th>
                                <th>Judul Arsip</th>
                                <th>Sub Bagian</th>
                                <th>Tahun</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($arsipTerbaru as $arsip)
                            <tr>
                                <td><strong>{{ $arsip->kodeKlasifikasi->kode ?? 'N/A' }}</strong></td>
                                <td>{{ Str::limit($arsip->uraian_arsip, 50) }}</td>
                                <td>{{ $arsip->subBagian->nama_sub_bagian ?? 'N/A' }}</td>
                                <td>{{ $arsip->tahun_arsip }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'AKTIF' => 'success',
                                            'INAKTIF' => 'warning',
                                            'HABIS_RETENSI' => 'danger',
                                            'MUSNAH' => 'dark',
                                            'PERMANEN' => 'info'
                                        ];
                                        $color = $statusColors[$arsip->status_arsip] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        {{ $arsip->status_arsip }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('arsip.show', $arsip->id) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('arsip.edit', $arsip->id) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($arsipTerbaru->count() == 0)
                    <div class="text-center py-4">
                        <i class="bi bi-folder-x fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada data arsip</p>
                        <a href="{{ route('arsip.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Tambah Arsip Pertama
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection