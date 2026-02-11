@extends('layouts.app')

@section('page-title', 'Dashboard Sub Bagian')
@section('page-subtitle', 'Ringkasan Arsip Sub Bagian Anda')

@section('content')

@if($arsipDitolak > 0)
<!-- Notifikasi Arsip Ditolak -->
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Perhatian!</strong> Ada {{ $arsipDitolak }} arsip yang ditolak dan memerlukan perhatian Anda.
     <a href="{{ route('subbagian.riwayat-pemindahan.index') }}?status_pindah=ditolak" class="alert-link">Lihat detail</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    <!-- Total Arsip -->
    <div class="col-xl-3 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Arsip</h6>
                        <h2 class="fw-bold text-primary mb-0">{{ $totalArsip }}</h2>
                        <small class="text-muted">Arsip Sub Bagian Anda</small>
                    </div>
                    <div class="icon-shape bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-archive text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Arsip Dipindahkan -->
    <div class="col-xl-3 col-lg-6 mb-4">
        <div class="card h-100">
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
    
    <!-- Arsip Ditolak -->
    <div class="col-xl-3 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Arsip Ditolak</h6>
                        <h2 class="fw-bold text-danger mb-0">{{ $arsipDitolak }}</h2>
                        <small class="text-danger">
                            {{ $totalArsip > 0 ? number_format(($arsipDitolak/$totalArsip)*100, 1) : 0 }}% dari total
                        </small>
                    </div>
                    <div class="icon-shape bg-danger bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-x-circle text-danger fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Arsip Belum Dipindahkan -->
    <div class="col-xl-3 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Belum Dipindahkan</h6>
                        <h2 class="fw-bold text-warning mb-0">{{ $arsipBelumDipindahkan }}</h2>
                        <small class="text-warning">
                            {{ $totalArsip > 0 ? number_format(($arsipBelumDipindahkan/$totalArsip)*100, 1) : 0 }}% dari total
                        </small>
                    </div>
                    <div class="icon-shape bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-clock text-warning fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Chart Distribusi (opsional) -->
@if(isset($arsipPerTahun) && $arsipPerTahun->count() > 0)
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i> Distribusi Arsip per Tahun</h5>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
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
        </div>
    </div>
</div>
@endif


@endsection