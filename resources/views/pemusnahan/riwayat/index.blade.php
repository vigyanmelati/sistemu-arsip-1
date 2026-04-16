@extends('layouts.app')

@section('title', 'Riwayat Pemusnahan Arsip')

@section('content')
<div class="container-fluid py-4">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="bi bi-clock-history text-primary me-2"></i>
                Riwayat Pemusnahan Arsip
            </h4>
            <p class="text-muted mb-0">
                <i class="bi bi-archive me-1"></i>
                Daftar kegiatan pemusnahan arsip yang telah dilaksanakan
            </p>
        </div>
        <div class="mt-2 mt-md-0">
            <span class="badge bg-secondary bg-opacity-10 text-secondary p-2">
                <i class="bi bi-database me-1"></i>
                Total: {{ $pemusnahans->count() }} Kegiatan
            </span>
        </div>
    </div>

    {{-- ================= STATISTIK CARD ================= --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Total Pemusnahan</h6>
                            <h3 class="mb-0 fw-bold">{{ $pemusnahans->count() }}</h3>
                        </div>
                        <i class="bi bi-trash3 fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Total Arsip Dimusnahkan</h6>
                            <h3 class="mb-0 fw-bold">{{ $pemusnahans->sum('details_count') }}</h3>
                        </div>
                        <i class="bi bi-archive fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="col-md-3 mb-3 mb-md-0">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Tahun Terbaru</h6>
                            <h3 class="mb-0 fw-bold">
                                {{ $pemusnahans->max('tahun') ?? '-' }}
                            </h3>
                        </div>
                        <i class="bi bi-calendar fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Tahun Tertua</h6>
                            <h3 class="mb-0 fw-bold">
                                {{ $pemusnahans->min('tahun') ?? '-' }}
                            </h3>
                        </div>
                        <i class="bi bi-calendar2 fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>

    {{-- ================= INFO CARD ================= --}}
    <div class="alert alert-secondary border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex">
            <div class="me-3">
                <i class="bi bi-info-circle-fill fs-4"></i>
            </div>
            <div>
                <strong class="d-block mb-1">Informasi</strong>
                <ul class="mb-0 ps-3">
                    <li>Setiap baris merupakan <strong>satu batch pemusnahan</strong></li>
                    <li>Data bersifat <strong>final & read-only</strong></li>
                    <li>Dokumen pemusnahan dapat diunduh pada halaman detail</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-table text-primary fs-5 me-2"></i>
                <h5 class="card-title mb-0 fw-semibold">Daftar Riwayat Pemusnahan</h5>
            </div>
            <p class="text-muted small mt-2 mb-0 ms-4 ps-1">
                Data pemusnahan arsip yang sudah selesai dilaksanakan
            </p>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="10%" class="text-center">Tahun</th>
                            <th width="20%">Tanggal Pemusnahan</th>
                            <th width="15%" class="text-center">Jumlah Arsip</th>
                            <th width="20%" class="text-center">Status</th>
                            <th width="20%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pemusnahans as $i => $item)
                        <tr>
                            <td class="text-center fw-semibold text-muted">{{ $i + 1 }}</td>
                            <td class="text-center">
                                <span class="px-3 py-2 rounded" style="background-color: #f1f5f9; font-size: 0.875rem;">
                                    <i class="bi bi-calendar me-1"></i>
                                    {{ $item->tahun }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar-check text-muted me-2"></i>
                                    <span>
                                        {{ \Carbon\Carbon::parse($item->tanggal_pemusnahan)->translatedFormat('d F Y') }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-primary border border-primary px-3 py-2 fw-normal">
                                    <i class="bi bi-archive me-1"></i>
                                    {{ $item->details_count }} Arsip
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 fw-normal">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Dimusnahkan
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('pemusnahan.riwayat.show', $item->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                <h6 class="text-muted">Belum ada riwayat pemusnahan</h6>
                                <small class="text-muted">Data pemusnahan yang sudah selesai akan muncul di sini</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.03);
        transition: all 0.2s ease;
    }
    
    .btn-sm {
        transition: all 0.2s ease;
    }
    
    .btn-sm:hover {
        transform: translateY(-1px);
    }
    
    .card {
        transition: transform 0.2s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
    }
    
    /* Soft badge styles */
    .badge.bg-light.text-primary {
        color: #0d6efd !important;
    }
    
    .badge.bg-danger.bg-opacity-10 {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }
    
    .badge.bg-danger.bg-opacity-10.text-danger {
        color: #dc3545 !important;
    }
</style>
@endpush