@extends('layouts.app')

@section('page-title', 'Manajemen Lokasi - Daftar Arsip')
@section('page-subtitle', 'Box: ' . $box)

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4">
    {{-- Breadcrumb navigasi --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('manajemen-lokasi.index') }}" class="text-decoration-none">Ruangan</a></li>
            <li class="breadcrumb-item"><a href="{{ route('manajemen-lokasi.rak', $ruangan) }}" class="text-decoration-none">{{ $ruanganLabel }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('manajemen-lokasi.box', [$ruangan, $rak]) }}" class="text-decoration-none">Rak {{ $rak }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Box {{ $box }}</li>
        </ol>
    </nav>

    {{-- Tombol kembali --}}
    <div class="mb-4">
        <a href="{{ route('manajemen-lokasi.box', [$ruangan, $rak]) }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Box
        </a>
    </div>

    {{-- Header informasi box --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-semibold mb-0">
                <i class="bi bi-archive-fill me-2 text-info"></i> Arsip dalam Box
            </h3>
            <p class="text-muted mt-1 mb-0">
                <strong>Box {{ $box }}</strong> — Ruangan: {{ $ruanganLabel }} | Rak: {{ $rak }}
            </p>
        </div>
        <div class="search-box" style="min-width: 250px;">
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

    {{-- Card daftar arsip - DI BIKIN LEBAR DAN FULL WIDTH --}}
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden w-100">
        <div class="card-header bg-gradient-primary text-white py-3" style="background: linear-gradient(135deg, #0d6efd, #0b5ed7);">
            <h5 class="mb-0 fw-semibold">
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
                                <th style="width: 10%">Kode Klasifikasi</th>
                                <th style="width: 28%">Judul Arsip</th>
                                <th style="width: 6%">Tahun</th>
                                <th style="width: 6%">Rak</th>
                                <th style="width: 6%">Box</th>
                                <th style="width: 12%">Lokasi Arsip</th>
                                <th style="width: 9%">Aktif Sampai</th>
                                <th style="width: 9%">Inaktif Sampai</th>
                                <th style="width: 8%">Status</th>
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
                            @endphp
                            <tr class="arsip-row" 
                                data-kode="{{ strtolower($arsip->kode_klasifikasi ?? '') }}" 
                                data-judul="{{ strtolower($arsip->uraian_arsip ?? '') }}" 
                                data-tahun="{{ $arsip->tahun_arsip ?? '' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $arsip->kode_klasifikasi ?? '-' }}</td>
                                <td>{{ $arsip->uraian_arsip ?: '-' }}</td>
                                <td>{{ $arsip->tahun_arsip ?? '-' }}</td>
                                <td>{{ $arsip->lokasi_rak ?? $rak ?? '-' }}</td>
                                <td>{{ $arsip->lokasi_box ?? $box ?? '-' }}</td>
                                <td>{{ $arsip->lokasi_arsip ?? ($ruanganLabel ?? '-') }}</td>
                                <td>{{ $arsip->aktif_sampai ? \Carbon\Carbon::parse($arsip->aktif_sampai)->translatedFormat('d/m/Y') : '-' }}</td>
                                <td>{{ $arsip->inaktif_sampai ? \Carbon\Carbon::parse($arsip->inaktif_sampai)->translatedFormat('d/m/Y') : '-' }}</td>
                                <td>
                                    <span class="badge {{ $statusBadge }} px-3 py-2 rounded-pill">
                                        {{ $statusDisplay }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('arsip.show', $arsip->id) }}" class="btn btn-sm btn-info text-white" title="Detail Arsip">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0 py-3 text-muted small">
                    <i class="bi bi-info-circle me-1"></i> Total: {{ $arsips->count() }} arsip
                </div>
            @else
                <div class="alert alert-info text-center py-5 m-3 border-0">
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
    /* Perbesar lebar card secara maksimal */
    .card {
        width: 100% !important;
        margin-left: 0;
        margin-right: 0;
    }
    /* Hilangkan padding kiri-kanan pada container agar card lebih lebar */
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    @media (min-width: 1400px) {
        .container-fluid {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
    }
    /* Hover tabel */
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
        transition: background-color 0.2s;
    }
    /* Gradient header card */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #0d6efd, #0b5ed7);
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
    /* Breadcrumb styling */
    .breadcrumb-item a {
        color: #0d6efd;
    }
    .breadcrumb-item.active {
        font-weight: 600;
        color: #6c757d;
    }
    /* Tabel lebih rapi dan lega */
    .table th, .table td {
        vertical-align: middle;
        white-space: normal;
        word-break: break-word;
    }
    /* Responsif untuk HP: tetap full width */
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
        }
        .table tbody td:last-child {
            justify-content: flex-end;
        }
        .table tbody td .badge, 
        .table tbody td .btn {
            margin-left: auto;
        }
        .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
    }
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