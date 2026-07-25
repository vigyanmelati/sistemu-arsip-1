@extends('layouts.app')

@section('page-title', 'Surat Masuk')
@section('page-subtitle', 'Pencatatan dan Pengelolaan Surat Masuk')

@section('content')
<div class="container-fluid px-0">
    <div class="row">
        <div class="col-12">
            <div class="card">
                {{-- Header Card with gradient --}}
               <div class="card-header">
<div class="d-flex justify-content-between align-items-center">
    <h4 class="mb-2 mb-md-0 fw-bold text-primary">
        <i class="fas fa-envelope-open-text me-2"></i> Surat Masuk
    </h4>

    <div class="action-buttons d-flex gap-2 flex-wrap">
        {{-- Import --}}
        <button class="btn btn-gradient-green d-flex align-items-center gap-2 shadow-sm"
                data-bs-toggle="modal"
                data-bs-target="#importModal">
            <i class="bi bi-cloud-upload-fill"></i><span>Import</span>
        </button>

        {{-- Export --}}
        <a href="{{ route('surat-masuk.export') }}"
           class="btn btn-gradient-purple d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-file-earmark-excel-fill"></i><span>Export</span>
        </a>

          <a href="{{ route('surat-masuk.index', ['filter' => 'duplikasi']) }}"
       class="btn btn-danger d-flex align-items-center gap-2">
        <i class="bi bi-files"></i>
        <span>Cek Duplikasi</span>
    </a>
        {{-- Tambah --}}
        <a href="{{ route('surat-masuk.create') }}"
           class="btn btn-orange d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-plus-circle-fill"></i><span>Tambah Surat</span>
        </a>
    </div>
</div>
</div>

                  <div class="alert alert-info border-0 shadow-sm mb-4">
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

                <div class="card-body">
                    {{-- Form pencarian yang lebih responsif --}}
                    <div class="mb-4">
                            <form method="GET" action="{{ route('surat-masuk.index') }}">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Cari No. Surat, Perihal, atau Instansi..." 
                                           value="{{ request('search') }}">
                                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
                                    @if(request('search'))
                                        <a href="{{ route('surat-masuk.index') }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-lg"></i> Reset
                                        </a>
                                    @endif
                                </div>
                            </form>
                    </div>
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
@if(request('filter') == 'duplikasi')

<div class="alert alert-warning shadow-sm rounded-4">

    <h5 class="fw-bold">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Ditemukan {{ $jumlahDuplikat }} data surat masuk yang terindikasi duplikat.
    </h5>

    <p class="mb-3">
        Silakan lakukan pengecekan terhadap data surat masuk yang ditampilkan.
        Hapus data yang terduplikasi dan sisakan satu data surat masuk yang benar.
    </p>

    <a href="{{ route('surat-masuk.index') }}"
       class="btn btn-danger rounded-pill">

        <i class="fas fa-times-circle me-1"></i>
        Reset Filter Duplikasi

    </a>

</div>

@endif
                    <div class="table-scroll-top" aria-label="Geser tabel secara horizontal"><div></div></div>
                    <div class="table-responsive surat-table-scroll" style="overflow-x:auto;-webkit-overflow-scrolling:touch">
                        <table class="table table-hover align-middle" style="min-width:1450px">
                            <thead>
                                <tr class="text-center">
                                    <th style="min-width:55px">No</th>
                                    <th style="min-width:110px">No. Agenda</th>
                                    <th style="min-width:130px">Tanggal Dokumen</th>
                                    <th style="min-width:150px">Tanggal Penyelesaian</th>
                                    <th style="min-width:180px">No. Surat</th>
                                    <th style="min-width:260px">Perihal</th>
                                    <th style="min-width:220px">Asal Dokumen</th>
                                    <th style="min-width:105px">Dokumen</th>
                                    <th style="min-width:180px">Keterangan</th>
                                   

                                    @if(request('filter') == 'duplikasi')
                                        <th>Keterangan Duplikasi</th>
                                    @endif

                                    
                                    <th style="min-width:370px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($surat as $item)
                                <tr class="text-center">
                                    <td class="fw-semibold">{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">
                                        {{ $item->nomor_agenda ?? '-' }}
                                        @if($item->sinar_v1_document_id)
                                            <span class="badge bg-secondary d-block mt-1">SINAR V1</span>
                                        @endif
                                    </td>
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
                                
                                        @if(request('filter') == 'duplikasi')

                                        <td>

                                        @if(($duplicateCounts[$item->nomor_dokumen] ?? 0) > 1)

                                        <button
                                        class="badge bg-danger border-0"
                                        style="cursor:pointer"
                                        data-bs-toggle="modal"
                                        data-bs-target="#duplicateModal{{$item->id}}">

                                        Duplikat

                                        </button>

                                        @else

                                        <span class="badge bg-success">
                                        Tidak Duplikat
                                        </span>

                                        @endif

                                        </td>

                                        @endif
                                        <td>
    <div class="d-flex gap-1 justify-content-center flex-nowrap surat-actions">

        <a href="{{ route('surat-masuk.show', $item->id) }}"
           class="btn btn-sm btn-info text-white"
           data-bs-toggle="tooltip"
           title="Detail">
            <i class="fas fa-eye me-1"></i> Detail
        </a>

        <a href="{{ route('surat-masuk.edit', $item->id) }}"
           class="btn btn-sm btn-warning"
           data-bs-toggle="tooltip"
           title="Edit">
            <i class="fas fa-edit me-1"></i> Edit
        </a>

        <a href="{{ route('surat-masuk.disposisi', $item->id) }}"
           target="_blank"
           class="btn btn-sm btn-primary"
           data-bs-toggle="tooltip"
           title="Cetak Disposisi">
            <i class="fas fa-print me-1"></i> Disposisi
        </a>

        <form action="{{ route('surat-masuk.destroy', $item->id) }}"
              method="POST"
              style="display:inline-block">
            @csrf
            @method('DELETE')

            <button type="submit"
                    class="btn btn-sm btn-danger"
                    data-bs-toggle="tooltip"
                    title="Hapus"
                    onclick="return confirm('Yakin ingin menghapus surat ini?')">
                <i class="fas fa-trash-alt me-1"></i> Hapus
            </button>
        </form>

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
                                </tr>

@if(($duplicateCounts[$item->nomor_dokumen] ?? 0) > 1)

<div class="modal fade"
     id="duplicateModal{{$item->id}}"
     tabindex="-1">

<div class="modal-dialog modal-lg">

<div class="modal-content rounded-4">

<div class="modal-header bg-danger text-white">

<h5 class="modal-title">

    Informasi Data Duplikat

</h5>

<button type="button"
        class="btn-close btn-close-white"
        data-bs-dismiss="modal">

</button>

</div>


<div class="modal-body">


<p>

Nomor Surat :

<strong>

{{ $item->nomor_dokumen }}

</strong>

</p>

<div class="alert alert-warning">

Data surat masuk ini terindikasi memiliki
nomor surat yang sama dengan data berikut.

</div>


<table class="table table-bordered">

<thead>

<tr>

<th>Baris</th>
<th>Perihal</th>
<th>Tanggal Dokumen</th>

</tr>

</thead>

<tbody>

@foreach(\App\Models\SuratMasuk::where('nomor_dokumen',$item->nomor_dokumen)->get() as $duplicate)

<tr>

<td>

{{ $loop->iteration }}

</td>

<td>

{{ $duplicate->perihal }}

</td>

<td>

{{ \Carbon\Carbon::parse($duplicate->tanggal_dokumen)->format('d/m/Y') }}

</td>

</tr>

@endforeach


</tbody>

</table>


<div class="alert alert-info mb-0">

<i class="fas fa-info-circle me-2"></i>

Silakan hapus data surat masuk yang terduplikasi dan sisakan satu data surat masuk yang paling benar.

</div>

</div>

</div>

</div>

</div>

@endif
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

     

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('surat-masuk.import') }}"
              method="POST"
              class="modal-content border-0 rounded-4">

            @csrf

            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-file-import me-2"></i>
                    Import Surat Masuk dari SINAR V1 Historis
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="alert alert-info border-0 rounded-4 mb-3">
                    Master instansi diimpor lebih dahulu, kemudian Surat Masuk kategori 12 beserta lampirannya.
                    Import ulang aman: data yang sudah pernah masuk akan diperbarui, bukan digandakan.
                </div>
                <div class="row g-2 text-center mb-3">
                    <div class="col-4"><div class="border rounded p-2"><strong class="d-block fs-5">{{ number_format($jumlahHistorisV1) }}</strong><small>Surat historis</small></div></div>
                    <div class="col-4"><div class="border rounded p-2"><strong class="d-block fs-5">{{ number_format($jumlahInstansiHistoris) }}</strong><small>Instansi historis</small></div></div>
                    <div class="col-4"><div class="border rounded p-2"><strong class="d-block fs-5">{{ number_format($jumlahSudahDiimport) }}</strong><small>Sudah diimpor</small></div></div>
                </div>
                @if($jumlahInstansiHistoris === 0)
                    <div class="alert alert-warning small">Detail tabel instansi historis belum tersedia. Jalankan ulang Import SINAR V1 agar alamat dan data kontak ikut terbaca. Nama instansi tetap dapat dibentuk dari data surat historis.</div>
                @endif
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="confirmation" value="1" id="confirmImportV1" required>
                    <label class="form-check-label" for="confirmImportV1">Saya memahami bahwa data Surat Masuk V1 akan dimasukkan atau diperbarui di daftar Surat Masuk.</label>
                </div>
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
                    Mulai Import SINAR V1
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
    .surat-pagination nav > div:first-child { display: none !important; }
    .surat-pagination nav > div:last-child {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .surat-pagination nav p { margin-bottom: 0; color: #6c757d; font-size: .875rem; }
    .surat-pagination .pagination { margin-bottom: 0; }
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

    /* Selaras dengan tabel Arsip Internal */
    .table-responsive::-webkit-scrollbar { height: 6px; }
    .table-responsive::-webkit-scrollbar-track { background: #f1f1f1; }
    .table-responsive::-webkit-scrollbar-thumb { background: #888; border-radius: 3px; }
    .table-responsive::-webkit-scrollbar-thumb:hover { background: #555; }
    .table-scroll-top {
        overflow-x: auto;
        overflow-y: hidden;
        height: 16px;
        margin-bottom: 6px;
        border-radius: 4px;
        scrollbar-color: #888 #f1f1f1;
        scrollbar-width: auto;
    }
    .table-scroll-top > div { height: 1px; }
    .table-scroll-top::-webkit-scrollbar { height: 12px; }
    .table-scroll-top::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
    .table-scroll-top::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
    .table thead th {
        background: #f8f9fa;
        color: #333;
        font-weight: 600;
        padding: 12px 10px;
        border-bottom: 2px solid #dee2e6;
        vertical-align: middle;
        position: sticky;
        top: 0;
        z-index: 20;
        box-shadow: inset 0 -2px 0 #dee2e6;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: none;
        box-shadow: none;
    }
    .table tbody td { background-color: #fff; color: #495057; }
    .table tbody tr:hover td { background-color: #f8f9fa; }
    .surat-actions .btn { white-space: nowrap; }
    .surat-table-scroll { max-height: 65vh; overflow: auto !important; }
    .btn-gradient-purple {
        background: linear-gradient(135deg, #9c27b0 0%, #673ab7 100%);
        border: none; color: #fff;
    }
    .btn-gradient-purple:hover { background: linear-gradient(135deg, #8e24aa 0%, #5e35b1 100%); color: #fff; transform: translateY(-1px); }
    .btn-gradient-green {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none; color: #fff;
    }
    .btn-gradient-green:hover { background: linear-gradient(135deg, #218838 0%, #1ba87e 100%); color: #fff; transform: translateY(-1px); }
    .btn-orange { background: #fd7e14; border-color: #fd7e14; color: #fff; }
    .btn-orange:hover { background: #e66a00; border-color: #e66a00; color: #fff; }
    .action-buttons .btn i { font-size: 1.1em; }
    @media (max-width: 768px) {
        .card-header > .d-flex { flex-direction: column; align-items: stretch !important; gap: 12px; }
        .card-header .action-buttons { width: 100%; }
        .card-header .action-buttons .btn { flex: 1; min-width: 125px; justify-content: center; }
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

        document.querySelectorAll('.table-scroll-top').forEach(function(topScroll) {
            const tableScroll = topScroll.nextElementSibling;
            const table = tableScroll?.querySelector('table');
            if (!table) return;
            const spacer = topScroll.firstElementChild;
            let syncing = false;
            const updateWidth = () => { spacer.style.width = table.scrollWidth + 'px'; };
            topScroll.addEventListener('scroll', () => {
                if (syncing) return; syncing = true;
                tableScroll.scrollLeft = topScroll.scrollLeft; syncing = false;
            });
            tableScroll.addEventListener('scroll', () => {
                if (syncing) return; syncing = true;
                topScroll.scrollLeft = tableScroll.scrollLeft; syncing = false;
            });
            updateWidth();
            window.addEventListener('resize', updateWidth);
            if (window.ResizeObserver) new ResizeObserver(updateWidth).observe(table);
        });
    });
</script>
@endpush
