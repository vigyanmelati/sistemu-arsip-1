@extends('layouts.app')

@section('page-title', 'Dashboard Tata Usaha')
@section('page-subtitle', 'Ringkasan Surat Masuk')

@section('content')

<div class="row">

    <!-- Total Surat Masuk -->
    <div class="col-xl-4 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Surat Masuk</h6>
                        <h2 class="fw-bold text-primary mb-0">{{ $totalSuratMasuk }}</h2>
                        <small class="text-muted">Seluruh Surat Masuk</small>
                    </div>
                    <div class="icon-shape bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-envelope-fill text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Surat Bulan Ini -->
    <div class="col-xl-4 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Surat Bulan Ini</h6>
                        <h2 class="fw-bold text-success mb-0">{{ $suratBulanIni }}</h2>
                        <small class="text-success">Surat diterima bulan ini</small>
                    </div>
                    <div class="icon-shape bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-calendar-check text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Surat Hari Ini -->
    <div class="col-xl-4 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Surat Hari Ini</h6>
                        <h2 class="fw-bold text-warning mb-0">{{ $suratHariIni }}</h2>
                        <small class="text-warning">Surat diterima hari ini</small>
                    </div>
                    <div class="icon-shape bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-calendar-day text-warning fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-envelope-paper me-2"></i>
            Selamat Datang
        </h5>
    </div>

    <div class="card-body">
        <h5>Halo, {{ Auth::user()->name }} 👋</h5>

        <p class="text-muted mb-0">
            Anda login sebagai <strong>Tata Usaha (TU)</strong>.
            Gunakan menu <strong>Surat Masuk</strong> untuk mengelola data surat,
            menambah, mengubah, menghapus, mengimpor, maupun mengekspor data surat masuk.
        </p>
    </div>
</div>

@endsection