@extends('layouts.app')

@section('page-title', 'Dashboard Sub Bagian')
@section('page-subtitle', 'Ringkasan Arsip Sub Bagian Anda')

@section('content')
<div class="row">

    <!-- Total Arsip -->
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Arsip</h5>
                <p class="card-text fs-2">{{ $totalArsip }}</p>
            </div>
        </div>
    </div>

    <!-- Arsip per Status -->
    @foreach($arsipPerStatus as $status => $count)
    @php
        // Pilih warna berbeda untuk setiap status
        $bgColor = match($status) {
            'AKTIF' => 'success',
            'INAKTIF' => 'secondary',
            'USUL_MUSNAH' => 'warning',
            'PERMANEN' => 'info',
            'MUSNAH' => 'danger',
            default => 'dark',
        };
    @endphp
    <div class="col-md-2">
        <div class="card text-white bg-{{ $bgColor }} mb-3">
            <div class="card-body text-center">
                <h6 class="card-title">{{ $status }}</h6>
                <p class="card-text fs-3">{{ $count }}</p>
            </div>
        </div>
    </div>
    @endforeach

</div>

<!-- Menu Akses Cepat -->
<div class="row mt-4">
    <div class="col-md-4">
        <a href="#" class="btn btn-primary w-100 py-3">
            <i class="bi bi-folder me-2"></i> Kelola Arsip Sub Bagian
        </a>
    </div>
</div>
@endsection
