@extends('layouts.app')

@section('page-title', 'Manajemen Lokasi - Daftar Arsip')
@section('page-subtitle', 'Box: ' . $box)

@section('content')
<div class="container-fluid px-4">
    {{-- Breadcrumb navigasi --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('subbagian.manajemen-lokasi.index') }}" class="text-decoration-none">Ruangan</a></li>
            <li class="breadcrumb-item"><a href="{{ route('subbagian.manajemen-lokasi.rak', $ruangan) }}" class="text-decoration-none">{{ $ruanganLabel }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('subbagian.manajemen-lokasi.box', [$ruangan, $rak]) }}" class="text-decoration-none">Rak {{ $rak }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Box {{ $box }}</li>
        </ol>
    </nav>

    {{-- Tombol kembali --}}
    <div class="mb-4">
        <a href="{{ route('subbagian.manajemen-lokasi.box', [$ruangan, $rak]) }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Box
        </a>
    </div>

    {{-- Header informasi box dengan desain card --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-wrapper rounded-4 d-inline-flex align-items-center justify-content-center"
                             style="width: 70px; height: 70px; background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(13, 202, 240, 0.1));">
                            <i class="bi bi-archive-fill fs-1" style="color: #0d6efd;"></i>
                        </div>
                        <div>
                            <h3 class="fw-semibold mb-1" style="color: #0d6efd;">Box {{ $box }}</h3>
                            <p class="text-muted mb-0">
                                <i class="bi bi-building me-1"></i> {{ $ruanganLabel }} 
                                <i class="bi bi-layers ms-2 me-1"></i> Rak {{ $rak }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <div class="search-box" style="min-width: 100%;">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="searchArsip" class="form-control border-start-0 ps-0" placeholder="Cari arsip...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Reset pencarian">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistik ringkas --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 stat-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Total Arsip</span>
                            <h3 class="fw-bold mb-0 text-primary">{{ $arsips->count() }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(13, 110, 253, 0.1);">
                            <i class="bi bi-archive fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 stat-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Aktif</span>
                            <h3 class="fw-bold mb-0 text-success">{{ $arsips->where('status_arsip', 'AKTIF')->count() }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(25, 135, 84, 0.1);">
                            <i class="bi bi-check-circle fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 stat-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Inaktif</span>
                            <h3 class="fw-bold mb-0 text-warning">{{ $arsips->where('status_arsip', 'INAKTIF')->count() }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(255, 193, 7, 0.1);">
                            <i class="bi bi-clock fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 stat-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Habis Retensi</span>
                            <h3 class="fw-bold mb-0 text-danger">{{ $arsips->where('status_arsip', 'HABIS_RETENSI')->count() }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(220, 53, 69, 0.1);">
                            <i class="bi bi-exclamation-triangle fs-4 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Card daftar arsip --}}
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header py-3" style="background: linear-gradient(135deg, #0d6efd, #0b5ed7);">
            <h5 class="mb-0 fw-semibold text-white">
                <i class="bi bi-box-seam me-2"></i> Daftar Arsip — Box {{ $box }}
            </h5>
        </div>
        <div class="card-body p-0">
            @if($arsips->count())
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-hover align-middle mb-0" id="arsipTable" style="min-width: 1000px; width: 100%;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 4%">#</th>
                                <th style="width: 15%">Kode Klasifikasi</th>
                                <th style="width: 35%">Judul Arsip</th>
                                <th style="width: 6%">Tahun</th>
                                <th style="width: 12%">Aktif Sampai</th>
                                <th style="width: 12%">Inaktif Sampai</th>
                                <th style="width: 10%">Status</th>
                                <th style="width: 6%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="arsipBody">
                            @foreach($arsips as $index => $arsip)
                            @php
                                $statusDisplay = str_replace('_', ' ', $arsip->status_arsip ?? 'AKTIF');
                                $statusBadge = match($statusDisplay) {
                                    'AKTIF' => 'bg-success',
                                    'INAKTIF' => 'bg-warning',
                                    'HABIS RETENSI' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                $statusIcon = match($statusDisplay) {
                                    'AKTIF' => 'bi-check-circle-fill',
                                    'INAKTIF' => 'bi-clock-fill',
                                    'HABIS RETENSI' => 'bi-exclamation-triangle-fill',
                                    default => 'bi-question-circle-fill'
                                };
                                // Ambil kode klasifikasi dari relasi
                                $kodeKlasifikasi = $arsip->kodeKlasifikasi;
                            @endphp
                            <tr class="arsip-row" 
                                data-kode="{{ strtolower($kodeKlasifikasi->kode ?? '') }}" 
                                data-judul="{{ strtolower($arsip->uraian_arsip ?? '') }}" 
                                data-tahun="{{ $arsip->tahun_arsip ?? '' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($kodeKlasifikasi)
                                        <div class="fw-semibold text-primary">{{ $kodeKlasifikasi->kode ?? '-' }}</div>
                                        <small class="text-muted d-block">{{ Str::limit($kodeKlasifikasi->uraian ?? '-', 40) }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-medium">{{ Str::limit($arsip->uraian_arsip ?? '-', 80) }}</div>
                                    @if($arsip->no_arsip)
                                        <small class="text-muted d-block">No. Arsip: {{ $arsip->no_arsip }}</small>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark">{{ $arsip->tahun_arsip ?? '-' }}</span></td>
                                <td>{{ $arsip->aktif_sampai ? \Carbon\Carbon::parse($arsip->aktif_sampai)->translatedFormat('d/m/Y') : '-' }}</td>
                                <td>{{ $arsip->inaktif_sampai ? \Carbon\Carbon::parse($arsip->inaktif_sampai)->translatedFormat('d/m/Y') : '-' }}</td>
                                <td>
                                    <span class="badge {{ $statusBadge }} px-3 py-2 rounded-pill">
                                        <i class="{{ $statusIcon }} me-1 fs-9"></i>
                                        {{ $statusDisplay }}
                                    </span>
                                </td>
                                <td class="text-center">
                                  <a href="{{ route('subbagian.arsip.show', $arsip->id) }}" class="btn btn-info" title="Detail" style="padding: 0.25rem 0.5rem;">
                                    <i class="bi bi-eye"></i>
                                </a> 
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0 py-3 text-muted small d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <i class="bi bi-info-circle me-1"></i> Total: {{ $arsips->count() }} arsip
                    </div>
                    <div>
                        <i class="bi bi-folder-symlink me-1"></i> Box {{ $box }} | Rak {{ $rak }} | {{ $ruanganLabel }}
                    </div>
                </div>
            @else
                <div class="alert alert-info text-center py-5 m-4 border-0 rounded-4">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    <h5 class="fw-semibold">Belum ada arsip di box ini</h5>
                    <p class="mb-0">Silakan tambah arsip melalui halaman manajemen arsip.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Pesan jika pencarian tidak menemukan hasil --}}
    <div id="noResultMessage" class="text-center py-5 d-none">
        <i class="bi bi-search-slash fs-1 text-muted"></i>
        <h5 class="mt-3">Arsip tidak ditemukan</h5>
        <p class="text-muted">Coba gunakan kata kunci lain.</p>
    </div>
</div>

<style>
    /* Breadcrumb styling */
    .breadcrumb-item a {
        color: #0d6efd;
        text-decoration: none;
        transition: color 0.2s;
    }
    
    .breadcrumb-item a:hover {
        color: #198754;
    }
    
    .breadcrumb-item.active {
        font-weight: 600;
        color: #0d6efd;
    }
    
    /* Search box styling */
    .search-box .input-group-text,
    .search-box .form-control {
        background-color: #fff;
        border: 1px solid #dee2e6;
    }
    
    .search-box .form-control:focus {
        box-shadow: none;
        border-color: #0d6efd;
    }
    
    .search-box .input-group-text {
        border-right: none;
    }
    
    .search-box .form-control {
        border-left: none;
    }
    
    .search-box .btn-outline-secondary {
        border-color: #dee2e6;
        color: #6c757d;
    }
    
    .search-box .btn-outline-secondary:hover {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #0d6efd;
    }
    
    /* Tabel styling */
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        font-weight: 600;
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    
    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.03);
        transition: background-color 0.2s;
    }
    
    /* Badge styling */
    .badge {
        font-weight: 500;
    }
    
    /* Statistik card hover */
    .stat-card {
        transition: all 0.3s ease;
        cursor: default;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important;
    }
    
    /* Icon wrapper */
    .icon-wrapper {
        transition: all 0.3s ease;
    }
    
    /* Responsif */
    @media (max-width: 768px) {
        .table thead {
            display: none;
        }
        
        .table tbody tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 0.75rem;
            background-color: #fff;
        }
        
        .table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: right;
            border: none;
            padding: 0.5rem 0;
        }
        
        .table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            text-align: left;
            flex: 1;
            margin-right: 1rem;
        }
        
        .table tbody td:last-child {
            justify-content: flex-end;
        }
        
        .table tbody td .badge, 
        .table tbody td .btn {
            margin-left: auto;
        }
        
        .container-fluid {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #0d6efd;
    }
    
    /* Animasi fade in */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .stat-card {
        animation: fadeInUp 0.5s ease backwards;
    }
    
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.1s; }
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .stat-card:nth-child(4) { animation-delay: 0.2s; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchArsip');
        const clearBtn = document.getElementById('clearSearch');
        const rows = document.querySelectorAll('#arsipBody .arsip-row');
        const noResultDiv = document.getElementById('noResultMessage');
        const tableContainer = document.querySelector('.table-responsive');

        function filterArsip() {
            const keyword = searchInput.value.toLowerCase().trim();
            let hasVisible = false;

            rows.forEach(row => {
                const kode = row.getAttribute('data-kode') || '';
                const judul = row.getAttribute('data-judul') || '';
                const tahun = row.getAttribute('data-tahun') || '';
                const text = `${kode} ${judul} ${tahun}`;
                
                if (text.includes(keyword) || keyword === '') {
                    row.style.display = '';
                    hasVisible = true;
                } else {
                    row.style.display = 'none';
                }
            });

            if (!hasVisible && rows.length > 0) {
                noResultDiv.classList.remove('d-none');
                if (tableContainer) tableContainer.style.display = 'none';
            } else {
                noResultDiv.classList.add('d-none');
                if (tableContainer) tableContainer.style.display = '';
            }
        }

        searchInput.addEventListener('keyup', filterArsip);
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            filterArsip();
            searchInput.focus();
        });

        // Set data labels untuk responsive
        function setDataLabels() {
            if (window.innerWidth <= 768) {
                const headers = document.querySelectorAll('#arsipTable thead th');
                const rows = document.querySelectorAll('#arsipTable tbody tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    cells.forEach((cell, idx) => {
                        if (headers[idx]) {
                            cell.setAttribute('data-label', headers[idx].innerText);
                        }
                    });
                });
            }
        }
        
        setDataLabels();
        window.addEventListener('resize', setDataLabels);
    });
</script>
@endsection