@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                {{-- Header Card with gradient --}}
               <div class="card-header bg-white border-0 py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
    <h4 class="mb-2 mb-md-0 fw-bold text-primary">
        <i class="fas fa-envelope-open-text me-2"></i> Surat Masuk
    </h4>

    <div class="d-flex gap-2 flex-wrap">
        {{-- Import --}}
        <button class="btn btn-success rounded-pill px-4 shadow-sm"
                data-bs-toggle="modal"
                data-bs-target="#importModal">
            <i class="fas fa-file-import me-1"></i> Import
        </button>

        {{-- Export --}}
        <a href="{{ route('surat-masuk.export') }}"
           class="btn btn-info text-white rounded-pill px-4 shadow-sm">
            <i class="fas fa-file-excel me-1"></i> Export
        </a>

        <!-- {{-- Tambah --}}
        <a href="{{ route('surat-masuk.create') }}"
           class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="fas fa-plus me-1"></i> Tambah Surat
        </a> -->
    </div>
</div>

                <div class="alert alert-info border-0 rounded-4 shadow-sm mx-3 mt-3 mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle fa-lg me-3 mt-1 text-primary"></i>

                        <div>
                            <h6 class="fw-bold mb-1">
                                Informasi Menu Surat Masuk
                            </h6>

                            <p class="mb-0 text-muted">
                               Menu ini digunakan untuk menginput dan mengelola seluruh <b> surat masuk </b>
                                yang berasal dari instansi luar, lembaga, maupun satuan kerja lainnya, 
                                serta melakukan pencetakan lembar disposisi surat untuk tindak lanjut.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-0">
                    {{-- Form pencarian yang lebih responsif --}}
                    <div class="row mb-4">
                        <div class="col-md-6 col-lg-5">
                            <form method="GET" action="{{ route('subbagian.surat-masuk.index') }}" class="d-flex gap-2">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 rounded-pill rounded-end">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control border-start-0 rounded-pill rounded-start" 
                                           placeholder="Cari No. Surat, Perihal, atau Instansi..." 
                                           value="{{ request('search') }}">
                                    @if(request('search'))
                                        <a href="{{ route('subbagian.surat-masuk.index') }}" class="btn btn-outline-secondary rounded-pill ms-2">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    @endif
                                </div>
                                <button class="btn btn-primary rounded-pill px-4" type="submit">Cari</button>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-center">
                                    <th width="5%" class="border-0 rounded-start">No</th>
                                    <th width="8%" class="border-0">No. Agenda</th>
                                    <th width="12%" class="border-0">Tanggal Dokumen</th>
                                    <th width="12%" class="border-0">Tanggal Penyelesaian</th>
                                    <th width="12%" class="border-0">No. Surat</th>
                                    <th width="15%" class="border-0">Perihal</th>
                                    <th width="12%" class="border-0">Asal Dokumen</th>
                                    <th width="10%" class="border-0">Dokumen</th>
                                    <th width="12%" class="border-0">Keterangan</th>
                                    <th width="15%" class="border-0 rounded-end">Aksi</th>
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
                                            class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="fas fa-file-pdf me-1"></i> Lihat
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($item->catatan, 30, '...') ?? '-' }}</td>
                                    <td>
    <div class="d-flex gap-2 justify-content-center">

        <a href="{{ route('subbagian.surat-masuk.show', $item->id) }}"
           class="btn btn-sm btn-outline-secondary rounded-pill px-3"
           data-bs-toggle="tooltip"
           title="Detail">
            <i class="fas fa-eye me-1"></i> Detail
        </a>

        <!-- <a href="{{ route('subbagian.surat-masuk.edit', $item->id) }}"
           class="btn btn-sm btn-outline-warning rounded-pill px-3"
           data-bs-toggle="tooltip"
           title="Edit">
            <i class="fas fa-edit me-1"></i> Edit
        </a>

        <a href="{{ route('subbagian.surat-masuk.disposisi', $item->id) }}"
           target="_blank"
           class="btn btn-sm btn-outline-info rounded-pill px-3"
           data-bs-toggle="tooltip"
           title="Cetak Disposisi">
            <i class="fas fa-print me-1"></i> Disposisi
        </a>

        <form action="{{ route('subbagian.surat-masuk.destroy', $item->id) }}"
              method="POST"
              style="display:inline-block">
            @csrf
            @method('DELETE')

            <button type="submit"
                    class="btn btn-sm btn-outline-danger rounded-pill px-3"
                    data-bs-toggle="tooltip"
                    title="Hapus"
                    onclick="return confirm('Yakin ingin menghapus surat ini?')">
                <i class="fas fa-trash-alt me-1"></i> Hapus
            </button>
        </form> -->

    </div>
</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
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
                        <div class="d-flex justify-content-center mt-4">
                            {{ $surat->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('surat-masuk.import') }}"
              method="POST"
              enctype="multipart/form-data"
              class="modal-content border-0 rounded-4">

            @csrf

            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-file-import me-2"></i>
                    Import Surat Masuk
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- Download Template --}}
                <div class="alert alert-info border-0 rounded-4 mb-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                        <div>
                            <div class="fw-bold mb-1">
                                Template Excel Import
                            </div>

                            <small class="text-muted">
                                Download template terlebih dahulu agar format sesuai sistem.
                            </small>
                        </div>

                       <a href="{{ asset('template/template_import_surat_masuk.xlsx') }}"
                        download
                        class="btn btn-primary rounded-pill">
                            <i class="fas fa-download me-1"></i>
                            Download Template
                        </a>
                    </div>
                </div>

                <label class="form-label fw-semibold">
                    Upload File Excel
                </label>

                <input type="file"
                       name="file"
                       class="form-control"
                       accept=".xlsx,.xls,.csv"
                       required>

                <small class="text-muted">
                    Format yang didukung: xlsx, xls, csv
                </small>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">
                    Batal
                </button>

                <button type="submit"
                        class="btn btn-success">
                    <i class="fas fa-upload me-1"></i>
                    Import
                </button>
            </div>

        </form>
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
    });
</script>
@endpush