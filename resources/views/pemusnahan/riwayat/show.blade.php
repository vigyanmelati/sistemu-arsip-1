@extends('layouts.app')

@section('title', 'Detail Riwayat Pemusnahan')

@section('content')
<div class="container-fluid py-4">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="bi bi-archive text-danger me-2"></i>
                Detail Pemusnahan Arsip
            </h4>
            <p class="text-muted mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Informasi lengkap kegiatan pemusnahan arsip
            </p>
        </div>

        <a href="{{ route('pemusnahan.riwayat') }}"
           class="btn btn-outline-secondary mt-2 mt-md-0">
            <i class="bi bi-arrow-left me-2"></i>
            Kembali
        </a>
    </div>

    {{-- ================= INFO PEMUSNAHAN ================= --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                    <i class="bi bi-info-circle text-primary"></i>
                </div>
                <h5 class="card-title mb-0 fw-semibold">Informasi Pemusnahan</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-info bg-opacity-10 p-2 me-3">
                            <i class="bi bi-calendar text-info"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Tahun</small>
                            <span class="fw-semibold">{{ $pemusnahan->tahun }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-2 me-3">
                            <i class="bi bi-calendar-check text-success"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Tanggal Pemusnahan</small>
                            <span class="fw-semibold">
                                {{ \Carbon\Carbon::parse($pemusnahan->tanggal_pemusnahan)->translatedFormat('d F Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-2 me-3">
                            <i class="bi bi-flag text-danger"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Status</small>
                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">
                                <i class="bi bi-check-circle me-1"></i>
                                DIMUSNAHKAN
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-3">
                            <i class="bi bi-archive text-warning"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Arsip</small>
                            <span class="fw-semibold">{{ $pemusnahan->details->count() }} Arsip</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($pemusnahan->catatan_anri)
            <div class="mt-4 pt-2">
                <div class="alert alert-warning border-0">
                    <div class="d-flex">
                        <i class="bi bi-chat-text fs-5 me-3"></i>
                        <div>
                            <strong class="d-block mb-1">Catatan ANRI</strong>
                            {{ $pemusnahan->catatan_anri }}
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ================= DOKUMEN PERSETUJUAN ================= --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-2 me-3">
                    <i class="bi bi-file-check text-success"></i>
                </div>
                <h5 class="card-title mb-0 fw-semibold">Dokumen Persetujuan</h5>
            </div>
            <p class="text-muted small mt-2 mb-0 ms-5 ps-1">
                Dokumen persetujuan dari ANRI dan KPU RI
            </p>
        </div>
        <div class="card-body">
            <div class="row g-4">
                {{-- Persetujuan ANRI --}}
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                                <i class="bi bi-building text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold">Persetujuan ANRI</h6>
                                <small class="text-muted">Dokumen persetujuan dari ANRI</small>
                            </div>
                        </div>
                        
                        @php
                            $fileAnri = $pemusnahan->file_persetujuan_anri ?? null;
                        @endphp
                        
                        @if($fileAnri)
                            <div class="alert alert-success border-0 mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-file-pdf fs-4 me-2 text-danger"></i>
                                        <span class="small">{{ basename($fileAnri) }}</span>
                                    </div>
                                    <a href="{{ asset('storage/'.$fileAnri) }}" 
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> Lihat
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-secondary border-0 mb-0">
                                <i class="bi bi-dash-circle me-2"></i>
                                <span class="small">Dokumen belum tersedia</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Persetujuan KPU --}}
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-danger bg-opacity-10 p-2 me-3">
                                <i class="bi bi-check2-circle text-danger"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold">Persetujuan KPU RI</h6>
                                <small class="text-muted">Dokumen persetujuan dari KPU RI</small>
                            </div>
                        </div>
                        
                        @php
                            $fileKpu = $pemusnahan->file_persetujuan_kpu ?? null;
                        @endphp
                        
                        @if($fileKpu)
                            <div class="alert alert-success border-0 mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-file-pdf fs-4 me-2 text-danger"></i>
                                        <span class="small">{{ basename($fileKpu) }}</span>
                                    </div>
                                    <a href="{{ asset('storage/'.$fileKpu) }}" 
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> Lihat
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-secondary border-0 mb-0">
                                <i class="bi bi-dash-circle me-2"></i>
                                <span class="small">Dokumen belum tersedia</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= DOKUMEN EKSEKUSI ================= --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-danger bg-opacity-10 p-2 me-3">
                    <i class="bi bi-fire text-danger"></i>
                </div>
                <h5 class="card-title mb-0 fw-semibold">Dokumen Eksekusi Pemusnahan</h5>
            </div>
            <p class="text-muted small mt-2 mb-0 ms-5 ps-1">
                Dokumen legal pelaksanaan pemusnahan arsip
            </p>
        </div>
        <div class="card-body">
            <div class="row g-4">
                {{-- Berita Acara Pemusnahan --}}
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-info bg-opacity-10 p-2 me-3">
                                <i class="bi bi-file-text text-info"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold">Berita Acara Pemusnahan</h6>
                                <small class="text-muted">Dokumen berita acara pelaksanaan</small>
                            </div>
                        </div>
                        
                        @php
                            $fileBa = $pemusnahan->file_berita_acara ?? null;
                        @endphp
                        
                        @if($fileBa)
                            <div class="alert alert-success border-0 mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-file-pdf fs-4 me-2 text-danger"></i>
                                        <span class="small">{{ basename($fileBa) }}</span>
                                    </div>
                                    <a href="{{ asset('storage/'.$fileBa) }}" 
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> Lihat
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-secondary border-0 mb-0">
                                <i class="bi bi-dash-circle me-2"></i>
                                <span class="small">Dokumen belum tersedia</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- SK Pemusnahan --}}
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-3">
                                <i class="bi bi-file-earmark-text text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold">SK Pemusnahan</h6>
                                <small class="text-muted">Surat Keputusan Pemusnahan</small>
                            </div>
                        </div>
                        
                        @php
                            $fileSk = $pemusnahan->file_sk_pemusnahan ?? null;
                        @endphp
                        
                        @if($fileSk)
                            <div class="alert alert-success border-0 mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-file-pdf fs-4 me-2 text-danger"></i>
                                        <span class="small">{{ basename($fileSk) }}</span>
                                    </div>
                                    <a href="{{ asset('storage/'.$fileSk) }}" 
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> Lihat
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-secondary border-0 mb-0">
                                <i class="bi bi-dash-circle me-2"></i>
                                <span class="small">Dokumen belum tersedia</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= DAFTAR ARSIP ================= --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                        <i class="bi bi-list-ul text-primary"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0 fw-semibold">Daftar Arsip yang Dimusnahkan</h5>
                        <p class="text-muted small mb-0 mt-1">Detail arsip yang telah dimusnahkan</p>
                    </div>
                </div>
                <div class="mt-2 mt-md-0">
                    <span class="badge bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-fire me-1"></i> Total: {{ $pemusnahan->details->count() }} Arsip
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="30%">Uraian Arsip</th>
                            <th width="10%" class="text-center">Tahun</th>
                            <th width="10%" class="text-center">Jumlah</th>
                            <th width="15%">Tingkat</th>
                            <th width="15%">Retensi</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pemusnahan->details as $i => $detail)
                        <tr>
                            <td class="text-center fw-semibold text-muted">{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-text text-muted me-2"></i>
                                    <span>{{ $detail->arsip->uraian_arsip }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="px-2 py-1 rounded" style="background-color: #f1f5f9; font-size: 0.8rem;">
                                    {{ $detail->arsip->tahun_arsip }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark fw-normal">
                                    {{ $detail->arsip->jumlah_berkas }} {{ $detail->arsip->satuan_arsip }}
                                </span>
                            </td>
                            <td>
                                <span class="small text-muted">
                                    {{ ucfirst($detail->arsip->tingkat_perkembangan) }}
                                </span>
                            </td>
                            <td>
                                <span class="small text-muted">
                                    Aktif {{ $detail->arsip->aktif_tahun }} / 
                                    Inaktif {{ $detail->arsip->inaktif_tahun }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('arsip.show', $detail->arsip->id) }}"
                                   class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
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
    
    .border {
        transition: all 0.2s ease;
    }
    
    .border:hover {
        border-color: #0d6efd !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
</style>
@endpush