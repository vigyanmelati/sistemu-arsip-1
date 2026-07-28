@extends('layouts.app')

@section('page-title', 'Surat Masuk')
@section('page-subtitle', 'Daftar Surat Masuk KPU Provinsi Bali')

@section('content')
<div class="container-fluid px-0">
    <div class="row">
        <div class="col-12">
            <div class="card">
                {{-- Header Card with gradient --}}
               <div class="card-header">
    <h4 class="mb-2 mb-md-0 fw-bold text-primary">
        <i class="fas fa-envelope-open-text me-2"></i> Surat Masuk
    </h4>

</div>

                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle fa-lg me-3 mt-1 text-primary"></i>

                        <div>
                            <h6 class="fw-bold mb-1">
                                Informasi Menu Surat Masuk
                            </h6>

                            <p class="mb-0 text-muted">
                               Menu ini menampilkan seluruh <b>surat masuk</b> dari instansi luar,
                               lembaga, maupun satuan kerja lainnya. Pencatatan dan koreksi data
                               dilakukan oleh petugas TU atau administrator.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Form pencarian yang lebih responsif --}}
                    <div class="mb-4">
                            <form method="GET" action="{{ route('subbagian.surat-masuk.index') }}">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Cari No. Surat, Perihal, atau Instansi..." 
                                           value="{{ request('search') }}">
                                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
                                    @if(request('search'))
                                        <a href="{{ route('subbagian.surat-masuk.index') }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-lg"></i> Reset
                                        </a>
                                    @endif
                                </div>
                            </form>
                    </div>
{{-- ALERT ERROR IMPORT --}}
@if(session('import_errors'))
    <div class="alert alert-danger shadow-sm rounded-4">
        <h6 class="fw-bold mb-2">
            <i class="fas fa-times-circle me-2"></i>
            {{ session('error') }}
        </h6>

        <ul class="mb-0 ps-3">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- SUCCESS --}}
@if(session('success'))
    <div class="alert alert-success shadow-sm rounded-4">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

                    {{-- Helper untuk link sorting header tabel --}}
                    @php
                        $currentSort = request('sort', 'created_at');
                        $currentDir  = request('direction', 'desc');
                        $sortUrl = function (string $field) use ($currentSort, $currentDir) {
                            $newDir = ($currentSort === $field && $currentDir === 'asc') ? 'desc' : 'asc';
                            return request()->fullUrlWithQuery(['sort' => $field, 'direction' => $newDir]);
                        };
                        $sortIcon = function (string $field) use ($currentSort, $currentDir) {
                            if ($currentSort !== $field) {
                                return '<i class="fas fa-sort text-muted ms-1"></i>';
                            }
                            return $currentDir === 'asc'
                                ? '<i class="fas fa-sort-up ms-1"></i>'
                                : '<i class="fas fa-sort-down ms-1"></i>';
                        };
                    @endphp

                    <div class="table-scroll-top" aria-label="Geser tabel secara horizontal"><div></div></div>
                    <div class="table-responsive surat-table-scroll" style="overflow-x:auto;-webkit-overflow-scrolling:touch">
                        <table class="table table-hover align-middle" style="min-width:1450px">
                            <thead>
                                <tr class="text-center">
                                    <th style="min-width:55px">No</th>
                                    <th style="min-width:110px">
                                        <a href="{{ $sortUrl('nomor_agenda') }}" class="text-decoration-none text-dark">
                                            No. Agenda {!! $sortIcon('nomor_agenda') !!}
                                        </a>
                                    </th>
                                    <th style="min-width:130px">
                                        <a href="{{ $sortUrl('tanggal_dokumen') }}" class="text-decoration-none text-dark">
                                            Tanggal Dokumen {!! $sortIcon('tanggal_dokumen') !!}
                                        </a>
                                    </th>
                                    <th style="min-width:150px">
                                        <a href="{{ $sortUrl('tanggal_penyelesaian') }}" class="text-decoration-none text-dark">
                                            Tanggal Penyelesaian {!! $sortIcon('tanggal_penyelesaian') !!}
                                        </a>
                                    </th>
                                    <th style="min-width:180px">
                                        <a href="{{ $sortUrl('nomor_dokumen') }}" class="text-decoration-none text-dark">
                                            No. Surat {!! $sortIcon('nomor_dokumen') !!}
                                        </a>
                                    </th>
                                    <th style="min-width:260px">
                                        <a href="{{ $sortUrl('perihal') }}" class="text-decoration-none text-dark">
                                            Perihal {!! $sortIcon('perihal') !!}
                                        </a>
                                    </th>
                                    <th style="min-width:220px">
                                        <a href="{{ $sortUrl('instansi_satker') }}" class="text-decoration-none text-dark">
                                            Asal Dokumen {!! $sortIcon('instansi_satker') !!}
                                        </a>
                                    </th>
                                    <th style="min-width:105px">Dokumen</th>
                                    <th style="min-width:200px">Tujuan Disposisi</th>
                                    <th style="min-width:180px">Keterangan</th>
                                    <th style="min-width:170px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($surat as $item)
                                <tr class="text-center">
                                    <td class="fw-semibold">{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">{{ $item->nomor_agenda ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal_dokumen)->translatedFormat('d M Y') }}</td>
                                    <td>{{ $item->tanggal_penyelesaian ? \Carbon\Carbon::parse($item->tanggal_penyelesaian)->translatedFormat('d M Y') : '-' }}</td>
                                    <td class="fw-semibold">{{ $item->nomor_dokumen ?? '-' }}</td>
                                    <td class="text-start">{{ $item->perihal ?? '-' }}</td>
                                    <td>{{ $item->instansi_satker ?? '-' }}</td>
                                  <td>
                                        @if($item->file_input)
                                            <a href="{{ asset('storage/surat_masuk/' . $item->file_input) }}" 
                                            target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file-pdf me-1"></i> Lihat
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @forelse($item->tujuanDisposisis as $tujuan)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1 mb-1">{{ $tujuan->nama_tujuan }}</span>
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </td>
                                    <td>{{ Str::limit($item->catatan, 30, '...') ?? '-' }}</td>
                                    <td>
    <div class="d-flex gap-1 justify-content-center">

        <a href="{{ route('subbagian.surat-masuk.show', $item->id) }}"
           class="btn btn-sm btn-info text-white"
           data-bs-toggle="tooltip"
           title="Detail">
            <i class="fas fa-eye me-1"></i> Detail
        </a>

        <a href="{{ route('subbagian.surat-masuk.disposisi', $item->id) }}"
           target="_blank"
           class="btn btn-sm btn-primary"
           data-bs-toggle="tooltip"
           title="Cetak Disposisi">
            <i class="fas fa-print me-1"></i> Disposisi
        </a>

    </div>
</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        Belum ada data surat masuk.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination dengan styling center --}}
                    @if(method_exists($surat, 'links') && $surat->hasPages())
                        <div class="surat-pagination mt-4">
                            {{ $surat->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    /* Perbaikan dan sentralisasi style */
    .table > :not(caption) > * > * {
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }
    .table tbody tr {
        transition: all 0.2s ease;
    }
    .table tbody tr:hover {
        background-color: #f8f9ff;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    /* Style tombol lebih halus */
    .btn-outline-warning:hover {
        background-color: #ffc107;
        color: #000;
        border-color: #ffc107;
    }
    .btn-outline-info:hover {
        background-color: #0dcaf0;
        color: #000;
        border-color: #0dcaf0;
    }
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: #fff;
        border-color: #dc3545;
    }
    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: #fff;
    }
    /* Header gradient */
    .card-header {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    }
    /* Radius card */
    .rounded-4 {
        border-radius: 1rem !important;
    }
    /* Header table style */
    .bg-light th {
        font-weight: 600;
        color: #1e466e;
        letter-spacing: 0.3px;
        border-bottom: 2px solid #e9ecef;
    }
    /* Rata tengah untuk seluruh header dan isi selain perihal */
    .table th, .table td:not(.text-start) {
        text-align: center;
    }
    /* Perihal tetap rata kiri agar mudah dibaca */
    .table td.text-start {
        text-align: left !important;
    }
    /* Pagination custom */
    .pagination {
        --bs-pagination-border-radius: 50rem;
        --bs-pagination-hover-bg: #e9ecef;
        --bs-pagination-active-bg: #0d6efd;
        --bs-pagination-active-border-color: #0d6efd;
    }
    .page-link {
        border-radius: 50rem !important;
        margin: 0 2px;
    }
    .surat-pagination nav > div:first-child { display:none !important; }
    .surat-pagination nav > div:last-child { display:flex !important; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
    .surat-pagination nav p { margin-bottom:0; color:#6c757d; font-size:.875rem; }
    .surat-pagination .pagination { margin-bottom:0; }
    /* Responsive tambahan */
    @media (max-width: 768px) {
        .table td, .table th {
            white-space: nowrap;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }
    .table-responsive::-webkit-scrollbar { height: 6px; }
    .table-responsive::-webkit-scrollbar-track { background: #f1f1f1; }
    .table-responsive::-webkit-scrollbar-thumb { background: #888; border-radius: 3px; }
    .table-scroll-top { overflow-x:auto; overflow-y:hidden; height:16px; margin-bottom:6px; border-radius:4px; scrollbar-color:#888 #f1f1f1; }
    .table-scroll-top > div { height:1px; }
    .table-scroll-top::-webkit-scrollbar { height:12px; }
    .table-scroll-top::-webkit-scrollbar-track { background:#f1f1f1; border-radius:4px; }
    .table-scroll-top::-webkit-scrollbar-thumb { background:#888; border-radius:4px; }
    .table thead th { background:#f8f9fa; color:#333; font-weight:600; padding:12px 10px; border-bottom:2px solid #dee2e6; position:sticky; top:0; z-index:20; box-shadow:inset 0 -2px 0 #dee2e6; }
    .table thead th a { display: inline-flex; align-items: center; }
    .table tbody tr:hover { background:#f8f9fa; transform:none; box-shadow:none; }
    .table tbody td { background:#fff; color:#495057; }
    .table tbody tr:hover td { background:#f8f9fa; }
    .surat-table-scroll { max-height:65vh; overflow:auto !important; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi tooltip Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
        document.querySelectorAll('.table-scroll-top').forEach(function(topScroll) {
            const tableScroll = topScroll.nextElementSibling;
            const table = tableScroll?.querySelector('table');
            if (!table) return;
            const spacer = topScroll.firstElementChild;
            let syncing = false;
            const updateWidth = () => { spacer.style.width = table.scrollWidth + 'px'; };
            topScroll.addEventListener('scroll', () => { if (syncing) return; syncing = true; tableScroll.scrollLeft = topScroll.scrollLeft; syncing = false; });
            tableScroll.addEventListener('scroll', () => { if (syncing) return; syncing = true; topScroll.scrollLeft = tableScroll.scrollLeft; syncing = false; });
            updateWidth();
            window.addEventListener('resize', updateWidth);
            if (window.ResizeObserver) new ResizeObserver(updateWidth).observe(table);
        });
    });
</script>
@endpush