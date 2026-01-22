@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Sistem Temu Arsip KPU Provinsi Bali')

@section('content')
<div class="row">
    <!-- Statistik Cards -->
    <div class="col-xl-3 col-lg-6 mb-4">
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
    </div>
    
    <div class="col-xl-3 col-lg-6 mb-4">
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
    </div>
    
    <div class="col-xl-3 col-lg-6 mb-4">
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
    </div>
    
    <div class="col-xl-3 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Usul Musnah</h6>
                        <h2 class="fw-bold text-danger mb-0">{{ $arsipMusnah }}</h2>
                        <small class="text-danger">
                            {{ $totalArsip > 0 ? number_format(($arsipMusnah/$totalArsip)*100, 1) : 0 }}% dari total
                        </small>
                    </div>
                    <div class="icon-shape bg-danger bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-trash text-danger fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Permanen</h6>
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
    </div>
    
    <div class="col-xl-3 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Perlu Tindak</h6>
                        <h2 class="fw-bold text-secondary mb-0">{{ $arsipPerluTindak }}</h2>
                        <small class="text-secondary">
                            <i class="bi bi-exclamation-triangle"></i> Butuh pemrosesan
                        </small>
                    </div>
                    <div class="icon-shape bg-secondary bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-clock-history text-secondary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart Distribusi -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i> Distribusi Arsip per Tahun</h5>
            </div>
            <div class="card-body">
                @if($arsipPerTahun->count() > 0)
                <div style="height: 300px;">
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
                @else
                <div class="text-center py-5">
                    <i class="bi bi-bar-chart text-muted fa-3x mb-3"></i>
                    <p class="text-muted">Belum ada data untuk ditampilkan</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Ringkasan Status -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i> Ringkasan Status</h5>
            </div>
            <div class="card-body">
                @if($totalArsip > 0)
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Aktif</span>
                        <span class="fw-bold">{{ $arsipAktif }}</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" style="width: {{ ($arsipAktif/$totalArsip)*100 }}%"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Inaktif</span>
                        <span class="fw-bold">{{ $arsipInaktif }}</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-warning" style="width: {{ ($arsipInaktif/$totalArsip)*100 }}%"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Usul Musnah</span>
                        <span class="fw-bold">{{ $arsipMusnah }}</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-danger" style="width: {{ ($arsipMusnah/$totalArsip)*100 }}%"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Permanen</span>
                        <span class="fw-bold">{{ $arsipPermanen }}</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-primary" style="width: {{ ($arsipPermanen/$totalArsip)*100 }}%"></div>
                    </div>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-pie-chart text-muted fa-3x mb-3"></i>
                    <p class="text-muted">Belum ada data arsip</p>
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
                                <td>{{ Str::limit($arsip->judul_arsip, 50) }}</td>
                                <td>{{ $arsip->subBagian->nama ?? 'N/A' }}</td>
                                <td>{{ $arsip->tahun_arsip }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'AKTIF' => 'success',
                                            'INAKTIF' => 'warning',
                                            'UMSUL_MUSNAH' => 'danger',
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