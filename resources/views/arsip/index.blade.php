@extends('layouts.app')

@section('page-title', 'Kelola Arsip')
@section('page-subtitle', 'Manajemen Data Arsip Digital')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-2 mb-md-0 fw-bold text-primary">
                <i class="bi bi-archive-fill me-2"></i> Arsip Internal
            </h4>
           <div class="action-buttons d-flex gap-2">
               <button type="button" class="btn btn-warning d-flex align-items-center gap-2 shadow-sm" id="updateStatusBtn">
                    <i class="bi bi-arrow-repeat"></i>
                    <span>Update Status Arsip</span>
                </button>
                <button class="btn btn-gradient-purple d-flex align-items-center gap-2 shadow-sm" id="openExportModal">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    <span>Export</span>
                </button>
                <button type="button" class="btn btn-gradient-green d-flex align-items-center gap-2 shadow-sm" id="openImportModal">
                    <i class="bi bi-cloud-upload-fill"></i>
                    <span>Import</span>
                </button>
                <button class="btn btn-cyan d-flex align-items-center gap-2" id="openFilterModal">
                    <i class="bi bi-funnel-fill"></i>
                    <span>Filter</span>
                </button>
               <a href="{{ route('arsip.index', ['show_duplicates' => 1]) }}" class="btn btn-danger d-flex align-items-center gap-2" id="checkDuplicatesBtn">
                    <i class="bi bi-files"></i>
                    <span>Cek Duplikasi</span>
                </a>
                <a href="{{ route('arsip.create') }}" class="btn btn-orange d-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Tambah Baru</span>
                </a>
            </div>
        </div>
    </div>

     <div class="alert alert-info border-0 shadow-sm mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-info-circle-fill fs-4 me-3 text-primary"></i>
        <div>
            <h6 class="fw-bold mb-1">Informasi Menu Arsip Internal</h6>
            <p class="mb-0">
                Menu Arsip Internal digunakan untuk mengelola seluruh <b> arsip yang dibuat oleh 
                KPU Provinsi Bali </b>, mulai dari surat dinas, berita acara, laporan, 
                dokumentasi kegiatan, hingga dokumen administrasi lainnya
            </p>
        </div>
    </div>
</div>
    
    <div class="card-body">
        <!-- Search Bar -->
        <form method="GET" action="{{ route('arsip.index') }}" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan kode, judul, atau sub bagian..." value="{{ request('search') }}">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </form>
        
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if(session('import_errors'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>❌ Data gagal diimport:</strong>
    <ul class="mb-0 mt-2">
        @foreach(session('import_errors') as $fail)
            <li>
                <strong>Baris {{ $fail->row() }}</strong> :
                {{ implode(', ', $fail->errors()) }}
            </li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
        @if(request('show_duplicates'))
<div class="alert alert-info mb-3 d-flex align-items-center">
    <i class="bi bi-info-circle-fill me-2"></i>
    Menampilkan arsip yang memiliki duplikat (berdasarkan judul dan tahun).
    <a href="{{ route('arsip.index', request()->except('show_duplicates')) }}" class="ms-auto btn btn-sm btn-outline-secondary">
        <i class="bi bi-x-lg"></i> Hapus filter
    </a>
</div>
@endif

       
        <!-- Table dengan horizontal scroll -->
        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="table table-hover" style="min-width: 1200px;">
                <thead>
                    <tr>
                        <th style="min-width: 50px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span>#</span>
                                @if(request('sort') == 'id')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>
                        <th style="min-width: 120px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'kode_klasifikasi', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span>Kode Klasifikasi</span>
                                @if(request('sort') == 'kode_klasifikasi')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>
                        <th style="min-width: 250px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'uraian_arsip', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span>Judul Arsip</span>
                                @if(request('sort') == 'uraian_arsip')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>
                        <th style="min-width: 80px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'tahun_arsip', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span>Tahun</span>
                                @if(request('sort') == 'tahun_arsip')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>
                        <th style="min-width: 80px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nomor_rak', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span>Rak</span>
                                @if(request('sort') == 'nomor_rak')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>
                        <th style="min-width: 80px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nomor_box', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span>Box</span>
                                @if(request('sort') == 'nomor_box')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>
                       <th style="min-width: 150px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery([
                                'sort' => 'lokasi_arsip',
                                'direction' => request('direction') == 'asc' ? 'desc' : 'asc'
                            ]) }}" 
                            class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                
                                <span>Lokasi Arsip</span>

                                @if(request('sort') == 'lokasi_arsip')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>
                        <th style="min-width: 100px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'aktif_tahun', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span>Aktif Tahun</span>
                                @if(request('sort') == 'aktif_tahun')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>

                        <th style="min-width: 120px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'inaktif_tahun', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span>Inaktif Tahun</span>
                                @if(request('sort') == 'inaktif_tahun')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>

                        <th style="min-width: 150px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'keterangan_jra', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span>Keterangan JRA</span>
                                @if(request('sort') == 'keterangan_jra')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>
                        <th style="min-width: 120px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'aktif_sampai', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span>Aktif Sampai</span>
                                @if(request('sort') == 'aktif_sampai')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>
                        <th style="min-width: 120px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'inaktif_sampai', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span>Inaktif Sampai</span>
                                @if(request('sort') == 'inaktif_sampai')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>
                        <th style="min-width: 100px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'status_arsip', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span>Status</span>
                                @if(request('sort') == 'status_arsip')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>
                        <th style="min-width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
              <tbody>
    @forelse($arsips as $arsip)
    @php
        // Cek apakah arsip sudah disetujui untuk dimusnahkan
        $isApprovedForDestruction = in_array($arsip->status_arsip, ['DISETUJUI_MUSNAH','MUSNAH']);
    @endphp
    <tr data-id="{{ $arsip->id }}"
        data-tanggal-arsip="{{ $arsip->tanggal_arsip }}"
        data-aktif-tahun="{{ $arsip->aktif_tahun }}"
        data-inaktif-tahun="{{ $arsip->inaktif_tahun }}"
        data-keterangan-jra="{{ $arsip->keterangan_jra }}">
        
        <!-- Kolom biasa -->
        <td>{{ $loop->iteration + ($arsips->currentPage() - 1) * $arsips->perPage() }}</td>
        <td><strong>{{ $arsip->kodeKlasifikasi->kode ?? 'N/A' }}</strong></td>
        
        <!-- Editable: Judul Arsip - disabled jika sudah disetujui musnah -->
        <td class="{{ !$isApprovedForDestruction ? 'editable' : '' }}" data-field="uraian_arsip">
            {{ Str::limit($arsip->uraian_arsip, 200) }}
        </td>
        
        <td>{{ $arsip->tahun_arsip }}</td>
        
        <!-- Editable: Rak - disabled jika sudah disetujui musnah -->
        <td class="{{ !$isApprovedForDestruction ? 'editable' : '' }}" data-field="nomor_rak">
            {{ $arsip->nomor_rak }}
        </td>
        
        <!-- Editable: Box - disabled jika sudah disetujui musnah -->
        <td class="{{ !$isApprovedForDestruction ? 'editable' : '' }}" data-field="nomor_box">
            {{ $arsip->nomor_box }}
        </td>
        
        <!-- Editable: Lokasi Arsip (dropdown) - disabled jika sudah disetujui musnah -->
        <td class="{{ !$isApprovedForDestruction ? 'editable-select' : '' }}" data-field="lokasi_arsip" data-value="{{ $arsip->lokasi_arsip }}">
            @php
                $lokasiLabel = [
                    'RECORD_CENTER_PERMANEN' => 'Record Center (Arsip Permanen)',
                    'RECORD_CENTER_INAKTIF' => 'Record Center (Arsip Inaktif)',
                ][$arsip->lokasi_arsip] ?? '-';
            @endphp
            {{ $lokasiLabel }}
        </td>
        
        <!-- Editable: Aktif Tahun - disabled jika sudah disetujui musnah -->
        <td class="{{ !$isApprovedForDestruction ? 'editable' : '' }}" data-field="aktif_tahun">
            {{ $arsip->aktif_tahun ?? '-' }}
        </td>
        
        <!-- Editable: Inaktif Tahun - disabled jika sudah disetujui musnah -->
        <td class="{{ !$isApprovedForDestruction ? 'editable' : '' }}" data-field="inaktif_tahun">
            {{ $arsip->inaktif_tahun ?? '-' }}
        </td>
        
        <!-- Editable: Keterangan JRA (dropdown) - disabled jika sudah disetujui musnah -->
        <td class="{{ !$isApprovedForDestruction ? 'editable-select' : '' }}" data-field="keterangan_jra" data-value="{{ $arsip->keterangan_jra }}">
            @php
                $jraLabel = [
                    'PERMANEN' => 'Permanen',
                    'MUSNAH' => 'Musnah',
                ][$arsip->keterangan_jra] ?? '-';
            @endphp
            {{ $jraLabel }}
        </td>
        
        <!-- Otomatis diupdate -->
        <td class="aktif-sampai-cell">{{ $arsip->aktif_sampai ? \Carbon\Carbon::parse($arsip->aktif_sampai)->format('d/m/Y') : '-' }}</td>
        <td class="inaktif-sampai-cell">{{ $arsip->inaktif_sampai ? \Carbon\Carbon::parse($arsip->inaktif_sampai)->format('d/m/Y') : '-' }}</td>
        
        <!-- Status + badge -->
        <td class="status-cell">
            @php
                $statusColors = [
                    'AKTIF' => 'success',
                    'INAKTIF' => 'warning',
                    'HABIS_RETENSI' => 'danger',
                    'PERMANEN' => 'info',
                    'DISETUJUI_MUSNAH' => 'danger'
                ];
                $color = $statusColors[$arsip->status_arsip] ?? 'secondary';
                $label = ($arsip->status_arsip == 'HABIS_RETENSI') ? 'HABIS RETENSI' : $arsip->status_arsip;
                $label = ($label == 'DISETUJUI_MUSNAH') ? 'DISETUJUI MUSNAH' : $label;
            @endphp
            <span class="badge bg-{{ $color }}">{{ $label }}</span>
        </td>
        
        <td class="text-center">
            <div class="btn-group btn-group-sm" style="gap: 6px;">
                <a href="{{ route('arsip.show', $arsip->id) }}" class="btn btn-info" title="Detail">
                    <i class="bi bi-eye"></i>
                </a>
                @if(!$isApprovedForDestruction)
                <a href="{{ route('arsip.edit', $arsip->id) }}" class="btn btn-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                </a>
                <form action="{{ route('arsip.destroy', $arsip->id) }}" method="POST" data-confirm="delete" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" title="Hapus">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
                @else
                <span class="text-muted small" style="font-size: 0.7rem;">
                    <i class="bi bi-lock-fill"></i> Tidak bisa diedit
                </span>
                @endif
            </div>
        </td>
    </tr>
    @empty
        <tr>
            <td colspan="14" class="text-center py-4">
                <i class="bi bi-folder-x fa-2x text-muted mb-2"></i>
                <p class="text-muted">Belum ada data arsip</p>
                <a href="{{ route('arsip.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Arsip Pertama
                </a>
            </td>
        </tr>
    @endforelse
</tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Menampilkan {{ $arsips->firstItem() }} - {{ $arsips->lastItem() }} dari {{ $arsips->total() }} arsip
            </div>
            
            <!-- Tambah class pagination-sm -->
            <nav aria-label="Page navigation">
                {{ $arsips->withQueryString()->links('pagination::bootstrap-5')->with('class', 'pagination pagination-sm mb-0') }}
            </nav>
        </div>
    </div>
</div>

<!-- MODAL OVERLAY - FIXED POSITION -->
<div class="modal-overlay" id="modalOverlay" style="display: none;"></div>

<!-- Filter Modal - CUSTOM MODAL -->
<div class="modal-container" id="filterModalContainer" style="display: none;">
    <div class="modal-content-wrapper">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-funnel me-2"></i> Filter Arsip
                </h5>
                <button type="button" class="btn-close-modal" id="closeFilterModal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form method="GET" action="{{ route('arsip.index') }}" id="filterForm">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Status Arsip</label>
                            <select name="status_arsip" class="form-select">
                                <option value="">Semua Status</option>
                                @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('status_arsip') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tahun Arsip</label>
                            <select name="tahun_arsip" class="form-select">
                                <option value="">Semua Tahun</option>
                                @foreach($tahunOptions as $tahun)
                                <option value="{{ $tahun }}" {{ request('tahun_arsip') == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Sub Bagian</label>
                            <select name="sub_bagian_id" class="form-select">
                                <option value="">Semua Sub Bagian</option>
                                @foreach($subBagianOptions as $subBagian)
                                <option value="{{ $subBagian->id }}" {{ request('sub_bagian_id') == $subBagian->id ? 'selected' : '' }}>
                                    {{ $subBagian->nama }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Kondisi Fisik</label>
                            <select class="form-select" name="keterangan">
                                <option value="">Semua Kondisi</option>
                                <option value="BAIK" {{ request('keterangan') == 'BAIK' ? 'selected' : '' }}>Baik</option>
                                <option value="RUSAK" {{ request('keterangan') == 'RUSAK' ? 'selected' : '' }}>Rusak</option>
                                <option value="HILANG" {{ request('keterangan') == 'HILANG' ? 'selected' : '' }}>Hilang</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Kode Klasifikasi</label>
                            <select name="kode_klasifikasi_id" class="form-select">
                                <option value="">Semua Kode</option>
                                @foreach($kodeKlasifikasiOptions as $kode)
                                <option value="{{ $kode->id }}" {{ request('kode_klasifikasi_id') == $kode->id ? 'selected' : '' }}>
                                    {{ $kode->kode }} - {{ Str::limit($kode->nama, 30) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Rak</label>
                            <input type="text" name="nomor_rak" class="form-control" value="{{ request('nomor_rak') }}" placeholder="Contoh: A-01">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Box</label>
                            <input type="text" name="nomor_box" class="form-control" value="{{ request('nomor_box') }}" placeholder="Contoh: B-01">
                        </div>

                                                <div class="col-md-6 mb-3">
    <label class="form-label fw-semibold">Aktif Tahun</label>
    <select name="aktif_tahun_kosong" class="form-select">
        <option value="">Semua</option>
        <option value="1" {{ request('aktif_tahun_kosong') == '1' ? 'selected' : '' }}>
            Belum Diisi
        </option>
        <option value="0" {{ request('aktif_tahun_kosong') == '0' ? 'selected' : '' }}>
            Sudah Diisi
        </option>
    </select>
</div>

<div class="col-md-6 mb-3">
    <label class="form-label fw-semibold">Inaktif Tahun</label>
    <select name="inaktif_tahun_kosong" class="form-select">
        <option value="">Semua</option>
        <option value="1" {{ request('inaktif_tahun_kosong') == '1' ? 'selected' : '' }}>
            Belum Diisi
        </option>
        <option value="0" {{ request('inaktif_tahun_kosong') == '0' ? 'selected' : '' }}>
            Sudah Diisi
        </option>
    </select>
</div>
                    </div>
                    
                    <div class="alert alert-info mt-3 mb-0 d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <div>
                            <strong>Tips:</strong> Gunakan filter untuk menyaring data sesuai kebutuhan. 
                            Anda dapat mengkombinasikan beberapa filter sekaligus.
                        </div>
                    </div>
                </div>
                 <div class="modal-footer bg-light d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" id="resetFilter">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset Filter
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Excel Modal - CUSTOM MODAL -->
<div class="modal-container" id="importModalContainer" style="display: none;">
    <div class="modal-content-wrapper">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-excel me-2"></i>
                    Import Data Arsip
                </h5>
                <button type="button" class="btn-close-modal" id="closeImportModal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form action="{{ route('arsip.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="import-icon-wrapper mb-3">
                            <i class="bi bi-cloud-upload text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="fw-semibold">Upload File Excel</h5>
                        <p class="text-muted">Pilih file Excel yang berisi data arsip</p>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">File Excel</label>
                        <div class="file-upload-area">
                            <input type="file" name="file_excel" class="form-control" id="excelFile" accept=".xlsx,.xls" required>
                            <div class="mt-2" id="fileInfo"></div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            Format yang didukung: .xls, .xlsx (Maksimal 5MB)
                        </small>
                    </div>

                    <div class="alert alert-warning d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div>
                            <strong>Perhatian!</strong> Pastikan format file sesuai dengan template sistem.
                            <a href="{{ asset('template/Contoh Format Arsip.xlsx') }}" 
                            class="alert-link d-block mt-1"
                            download>
                                <i class="bi bi-download me-1"></i> Download Template Excel
                            </a>

                        </div>
                    </div>
                </div>

                 <div class="modal-footer bg-light d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" id="cancelImport">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success" id="submitImport">
                        <i class="bi bi-upload me-1"></i> Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Export Excel Modal - CUSTOM MODAL -->
<div class="modal-container" id="exportModalContainer" style="display:none;">
    <div class="modal-content-wrapper" style="max-width:700px;">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-layout-text-sidebar-reverse me-2"></i>
                    Pilih Kolom Export
                </h5>
                <button type="button" class="btn-close-modal" id="closeExportModal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

           <form method="POST" action="{{ route('arsip.export') }}">
    @csrf
                {{-- kirim SEMUA query filter yang aktif --}}
                @foreach(request()->except('page') as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $val)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $val }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                <div class="modal-body">
                    <div class="row">
                        @php
                        $columns = [
                            'kode_klasifikasi' => 'Kode Klasifikasi',
                            'uraian_arsip' => 'Judul Arsip',
                            'jumlah' => 'Jumlah (Berkas+Satuan)',
                            'tahun_arsip' => 'Tahun',
                            'nomor_rak' => 'Rak',
                            'nomor_box' => 'Box',
                            'no_sampul' => 'No Sampul',
                            'aktif_sampai' => 'Aktif Sampai',
                            'inaktif_sampai' => 'Inaktif Sampai',
                            'status_arsip' => 'Status Arsip',
                            'sub_bagian' => 'Sub Bagian',
                            'keterangan' => 'Keterangan', 
                            'tingkat_perkembangan' => 'Tingkat Perkembangan',
                            'keterangan_jra' => 'Keterangan JRA',
                        ];
                        @endphp

                        @foreach($columns as $key => $label)
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input column-check"
                                       type="checkbox"
                                       name="columns[]"
                                       value="{{ $key }}"
                                       checked>
                                <label class="form-check-label fw-semibold">
                                    {{ $label }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <hr>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkAllColumns" checked>
                        <label class="form-check-label fw-bold">
                            Pilih Semua Kolom
                        </label>
                    </div>
                </div>

                <div class="modal-footer bg-light d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" id="cancelExport">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-download me-1"></i> Export Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Loading -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                <h5>Sedang Update Status...</h5>
                <p class="text-muted">Mohon tunggu, proses update sedang berjalan.</p>
                <div id="updateProgress" class="mt-2"></div>
            </div>
        </div>
    </div>
</div>

<!-- Duplikasi Modal - CUSTOM MODAL -->
<div class="modal-container" id="duplicateModalContainer" style="display: none;">
    <div class="modal-content-wrapper" style="max-width:800px;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-files me-2"></i>
                    Cek Duplikasi Data Arsip
                </h5>
                <button type="button" class="btn-close-modal" id="closeDuplicateModal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="duplicateResults">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memeriksa duplikasi...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" id="closeDuplicateModalFooter">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== STYLE UNTUK SORTING ===== */
    .sortable-header {
        cursor: pointer;
        transition: all 0.2s;
        user-select: none;
        background-color: #f8f9fa;
        position: relative;
    }
    
    .sortable-header:hover {
        background-color: #e9ecef;
    }
    
    .sortable-header a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 12px 10px;
        color: #333 !important;
        font-weight: 600;
        text-decoration: none !important;
    }
    
    .sortable-header i {
        font-size: 14px;
        transition: all 0.2s;
        visibility: visible !important;
        opacity: 1 !important;
        display: inline-block !important;
    }
    
    .sortable-header .bi-caret-up-down {
        color: #6c757d !important;
        opacity: 0.8;
    }
    
    .sortable-header .bi-caret-up-fill.text-dark,
    .sortable-header .bi-caret-down-fill.text-dark {
        color: #212529 !important;
        opacity: 1;
        font-weight: bold;
    }
    
    .sortable-header:hover .bi-caret-up-down {
        color: #495057 !important;
        opacity: 1;
    }
    
    /* ===== TOMBOL AKSI ===== */
    .btn-group-sm .btn,
    .btn-group-sm form {
        margin: 0 3px !important;
    }
    
    /* ===== SCROLLBAR ===== */
    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* ===== CUSTOM MODAL STYLES ===== */
    /* Overlay untuk modal */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        z-index: 9998;
        backdrop-filter: blur(3px);
    }
    
    /* Container untuk modal */
    .modal-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: flex;
        align-items: flex-start; 
        justify-content: center;
        padding-top: 50px; 
        pointer-events: none;
    }
    .modal-container.active {
        pointer-events: all;
    }
    
    /* Wrapper untuk konten modal */
    .modal-content-wrapper {
        max-width: 90%;
        max-height: 90vh;
        width: 900px;
        animation: modalSlideIn 0.3s ease-out;
        pointer-events: all;
    }
    
    /* Konten modal utama */
    .modal-content {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        max-height: 90vh;
    }
    
    /* Header modal */
    .modal-header {
        padding: 1.2rem 1.5rem;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        position: relative;
    }
    
    /* Tombol close modal */
    .btn-close-modal {
        position: absolute;
        right: 1.5rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 4px;
        transition: all 0.2s;
    }
    
    .btn-close-modal:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    /* Body modal */
    .modal-body {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
    max-height: calc(90vh - 140px); /* penting */
}

.modal-content {
    display: flex;
    flex-direction: column;
    max-height: 90vh;
}

.modal-body {
    scrollbar-width: thin;
}
    
    /* Footer modal */
    .modal-footer {
        padding: 1.2rem 1.5rem;
        border-top: 1px solid #dee2e6;
        background: #f8f9fa;
    }
    
    /* Form controls dalam modal */
    .modal-content .form-select,
    .modal-content .form-control {
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border-radius: 8px;
        border: 2px solid #e0e0e0;
        transition: all 0.3s;
    }
    
    .modal-content .form-select:focus,
    .modal-content .form-control:focus {
        border-color: #4dabf7;
        box-shadow: 0 0 0 0.25rem rgba(77, 171, 247, 0.25);
    }
    
    /* Alert dalam modal */
    .modal-content .alert {
        border-radius: 8px;
        border: none;
        padding: 1rem;
    }
    
    /* Import modal khusus */
    #importModalContainer .import-icon-wrapper {
        width: 80px;
        height: 80px;
        margin: 0 auto;
        background: #e8f5e9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    #importModalContainer .file-upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s;
    }
    
    #importModalContainer .file-upload-area:hover {
        border-color: #28a745;
        background-color: #f8f9fa;
    }
    
    /* Animasi modal */
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-20px) scale(0.95); /* sebelumnya -50px */
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @media (max-height: 600px) {
        .modal-container {
            padding-top: 20px;
            align-items: flex-start;
        }
    }

    
    /* Tombol dalam modal */
    .modal-footer .btn {
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .modal-footer .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }
    
    .modal-footer .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .modal-content-wrapper {
            max-width: 95%;
            width: 95%;
        }
        
        .modal-header, .modal-body, .modal-footer {
            padding: 1rem;
        }
        
        .card-header .d-flex {
            flex-direction: column;
            gap: 10px;
        }
        
        .card-header .action-buttons {
            width: 100%;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .card-header .action-buttons .btn {
            flex: 1;
            min-width: 120px;
            margin: 2px;
        }
    }
    
    /* Tampilan untuk modal yang lebih kecil */
    @media (max-width: 576px) {
        .modal-content-wrapper {
            max-width: 98%;
            width: 98%;
        }
        
        .modal-content {
            max-height: 95vh;
        }
    }
    
    /* Scrollbar untuk modal body */
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .modal-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }
    
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }


    /* Custom Button Colors */
    .btn-purple {
        background-color: #6f42c1;
        border-color: #6f42c1;
        color: white;
    }
    .btn-purple:hover {
        background-color: #5a32a3;
        border-color: #5a32a3;
        color: white;
    }

    .btn-info {
        background-color: #17a2b8;
        border-color: #17a2b8;
    }

    .btn-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }
    .btn-warning:hover {
        background-color: #e0a800;
        border-color: #e0a800;
        color: #212529;
    }

    /* Gradient Buttons */
    .btn-gradient-purple {
        background: linear-gradient(135deg, #9c27b0 0%, #673ab7 100%);
        border: none;
        color: white;
    }
    .btn-gradient-purple:hover {
        background: linear-gradient(135deg, #8e24aa 0%, #5e35b1 100%);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(156, 39, 176, 0.3);
    }

    .btn-gradient-green {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
    }
    .btn-gradient-green:hover {
        background: linear-gradient(135deg, #218838 0%, #1ba87e 100%);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    }

    .btn-cyan {
        background-color: #0dcaf0;
        border-color: #0dcaf0;
        color: white;
    }
    .btn-cyan:hover {
        background-color: #0bb5d4;
        border-color: #0bb5d4;
        color: white;
    }

    .btn-orange {
        background-color: #fd7e14;
        border-color: #fd7e14;
        color: white;
    }
    .btn-orange:hover {
        background-color: #e66a00;
        border-color: #e66a00;
        color: white;
    }

    /* Icon spacing */
    .action-buttons .btn i {
        font-size: 1.1em;
    }
    .editable, .editable-select {
    cursor: pointer;
    transition: background 0.2s;
}
.editable:hover, .editable-select:hover {
    background-color: #fff3cd !important;
}

/* Style untuk arsip yang tidak bisa diedit */
td:not(.editable):not(.editable-select) {
    background-color: #f8f9fa;
    color: #6c757d;
}

.text-muted i.bi-lock-fill {
    font-size: 0.8rem;
}

</style>

<script>
const openExportBtn = document.getElementById('openExportModal');
const exportModal = document.getElementById('exportModalContainer');
const closeExportBtn = document.getElementById('closeExportModal');
const cancelExportBtn = document.getElementById('cancelExport');
const checkAll = document.getElementById('checkAllColumns');
const columnChecks = document.querySelectorAll('.column-check');

openExportBtn.addEventListener('click', () => {
    modalOverlay.style.display = 'block';
    exportModal.style.display = 'flex';
    exportModal.classList.add('active');
});

function closeExportModal() {
    modalOverlay.style.display = 'none';
    exportModal.style.display = 'none';
    exportModal.classList.remove('active');
}

closeExportBtn.addEventListener('click', closeExportModal);
cancelExportBtn.addEventListener('click', closeExportModal);
modalOverlay.addEventListener('click', closeExportModal);

checkAll.addEventListener('change', function () {
    columnChecks.forEach(cb => cb.checked = this.checked);
});
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ==================== GLOBAL VARIABLES ====================
        const modalOverlay = document.getElementById('modalOverlay');
        const filterModalContainer = document.getElementById('filterModalContainer');
        const importModalContainer = document.getElementById('importModalContainer');
        const exportModalContainer = document.getElementById('exportModalContainer');
        // const duplicateModalContainer = document.getElementById('duplicateModalContainer');

        // ==================== UTILITY FUNCTIONS ====================
        function openModal(modalContainer) {
            // Hide all modals first
            filterModalContainer.style.display = 'none';
            importModalContainer.style.display = 'none';
            exportModalContainer.style.display = 'none';
            duplicateModalContainer.style.display = 'none';

            modalOverlay.style.display = 'block';
            modalContainer.style.display = 'flex';
            modalContainer.classList.add('active');
            document.body.classList.add('modal-open');
        }

        function closeAllModals() {
            modalOverlay.style.display = 'none';
            filterModalContainer.style.display = 'none';
            importModalContainer.style.display = 'none';
            exportModalContainer.style.display = 'none';
            duplicateModalContainer.style.display = 'none';
            document.body.classList.remove('modal-open');
        }

        // ==================== EXPORT MODAL ====================
        const openExportBtn = document.getElementById('openExportModal');
        const closeExportBtn = document.getElementById('closeExportModal');
        const cancelExportBtn = document.getElementById('cancelExport');
        const checkAll = document.getElementById('checkAllColumns');
        const columnChecks = document.querySelectorAll('.column-check');

        if (openExportBtn) {
            openExportBtn.addEventListener('click', () => openModal(exportModalContainer));
        }
        if (closeExportBtn) closeExportBtn.addEventListener('click', closeAllModals);
        if (cancelExportBtn) cancelExportBtn.addEventListener('click', closeAllModals);
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                columnChecks.forEach(cb => cb.checked = this.checked);
            });
        }

        // ==================== FILTER MODAL ====================
        const openFilterBtn = document.getElementById('openFilterModal');
        const closeFilterBtn = document.getElementById('closeFilterModal');
        const resetFilterBtn = document.getElementById('resetFilter');
        const filterForm = document.getElementById('filterForm');

        if (openFilterBtn) {
            openFilterBtn.addEventListener('click', (e) => {
                e.preventDefault();
                openModal(filterModalContainer);
            });
        }
        if (closeFilterBtn) closeFilterBtn.addEventListener('click', closeAllModals);
        if (resetFilterBtn) {
            resetFilterBtn.addEventListener('click', () => {
                window.location.href = "{{ route('arsip.index') }}";
            });
        }

        // ==================== IMPORT MODAL ====================
        const openImportBtn = document.getElementById('openImportModal');
        const closeImportBtn = document.getElementById('closeImportModal');
        const cancelImportBtn = document.getElementById('cancelImport');
        const importForm = document.getElementById('importForm');
        const excelFileInput = document.getElementById('excelFile');
        const fileInfo = document.getElementById('fileInfo');
        const submitImportBtn = document.getElementById('submitImport');

        if (openImportBtn) {
            openImportBtn.addEventListener('click', (e) => {
                e.preventDefault();
                openModal(importModalContainer);
            });
        }
        if (closeImportBtn) closeImportBtn.addEventListener('click', closeAllModals);
        if (cancelImportBtn) cancelImportBtn.addEventListener('click', closeAllModals);

        // File upload preview
        if (excelFileInput) {
            excelFileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                    const fileName = file.name;
                    const fileExtension = fileName.split('.').pop().toLowerCase();

                    const allowedExtensions = ['xlsx', 'xls'];
                    if (!allowedExtensions.includes(fileExtension)) {
                        fileInfo.innerHTML = `<div class="alert alert-danger">Format file tidak didukung.</div>`;
                        excelFileInput.value = '';
                        if (submitImportBtn) submitImportBtn.disabled = true;
                        return;
                    }

                    const maxSize = 5;
                    if (fileSize > maxSize) {
                        fileInfo.innerHTML = `<div class="alert alert-danger">Ukuran file terlalu besar (${fileSize} MB).</div>`;
                        excelFileInput.value = '';
                        if (submitImportBtn) submitImportBtn.disabled = true;
                        return;
                    }

                    fileInfo.innerHTML = `<div class="alert alert-success">File <strong>${fileName}</strong> (${fileSize} MB) siap diimport.</div>`;
                    if (submitImportBtn) submitImportBtn.disabled = false;
                }
            });
        }

        if (importForm) {
            importForm.addEventListener('submit', function(e) {
                const fileInput = this.querySelector('input[type="file"]');
                if (!fileInput || !fileInput.files.length) {
                    e.preventDefault();
                    alert('Silakan pilih file Excel terlebih dahulu.');
                    return;
                }
                if (submitImportBtn) {
                    submitImportBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Mengimport...';
                    submitImportBtn.disabled = true;
                }
            });
        }

        // ==================== CEK DUPLIKASI ====================
        // const checkDuplicatesBtn = document.getElementById('checkDuplicatesBtn');
        // const closeDuplicateModalBtns = document.querySelectorAll('#closeDuplicateModal, #closeDuplicateModalFooter');

        // if (checkDuplicatesBtn) {
        //     checkDuplicatesBtn.addEventListener('click', function() {
        //         openModal(duplicateModalContainer);

        //         const resultsDiv = document.getElementById('duplicateResults');
        //         if (!resultsDiv) {
        //             console.error('Elemen #duplicateResults tidak ditemukan!');
        //             return;
        //         }

        //         // Show loading
        //         resultsDiv.innerHTML = `
        //             <div class="text-center py-4">
        //                 <div class="spinner-border text-primary" role="status">
        //                     <span class="visually-hidden">Loading...</span>
        //                 </div>
        //                 <p class="mt-2">Memeriksa duplikasi...</p>
        //             </div>
        //         `;

        //         fetch('{{ route("arsip.check-duplicates") }}')
        //             .then(response => {
        //                 if (!response.ok) throw new Error(`HTTP ${response.status}`);
        //                 return response.json();
        //             })
        //             .then(data => {
        //                 console.log('Data duplikasi:', data); // DEBUG
        //                 if (data.duplicates.length === 0) {
        //                     resultsDiv.innerHTML = `
        //                         <div class="alert alert-success">
        //                             <i class="bi bi-check-circle-fill me-2"></i>
        //                             Tidak ditemukan data duplikat.
        //                         </div>
        //                     `;
        //                 } else {
        //                     let html = `<div class="alert alert-warning">
        //                                     <i class="bi bi-exclamation-triangle-fill me-2"></i>
        //                                     Ditemukan ${data.total} kelompok data duplikat (${data.total_records} arsip).
        //                                 </div>`;
        //                     data.duplicates.forEach(group => {
        //                         html += `<div class="card mb-3 border-danger">
        //                                     <div class="card-header bg-danger text-white">
        //                                         <strong>Duplikat ID: ${group.ids.join(', ')}</strong>
        //                                     </div>
        //                                     <div class="card-body">
        //                                         <p><strong>Judul Arsip:</strong> ${escapeHtml(group.uraian_arsip)}</p>
        //                                         <p><strong>Tahun:</strong> ${group.tahun_arsip}</p>
        //                                         <hr>
        //                                         <strong>Detail:</strong>
        //                                         <ul class="mt-2">
        //                                             ${group.records.map(record => `
        //                                                 <li>
        //                                                     ID: ${record.id} - 
        //                                                     <a href="${record.link}" target="_blank">Lihat Detail</a>
        //                                                 </li>
        //                                             `).join('')}
        //                                         </ul>
        //                                     </div>
        //                                 </div>`;
        //                     });
        //                     resultsDiv.innerHTML = html;
        //                 }
        //             })
        //             .catch(error => {
        //                 console.error('Error:', error);
        //                 resultsDiv.innerHTML = `
        //                     <div class="alert alert-danger">
        //                         <i class="bi bi-exclamation-triangle-fill me-2"></i>
        //                         Terjadi kesalahan saat memeriksa duplikasi.<br>
        //                         <small class="text-muted">${error.message}</small>
        //                     </div>
        //                 `;
        //             });
        //     });
        // }

        // if (closeDuplicateModalBtns.length) {
        //     closeDuplicateModalBtns.forEach(btn => {
        //         btn.addEventListener('click', closeAllModals);
        //     });
        // }

        // ==================== MODAL OVERLAY CLOSE ====================
        if (modalOverlay) {
            modalOverlay.addEventListener('click', closeAllModals);
        }

        // ==================== OTHER INITIALIZATIONS ====================
        // Prevent modal closing when clicking inside modal content
        const modalContainers = [filterModalContainer, importModalContainer, exportModalContainer, duplicateModalContainer];
        modalContainers.forEach(container => {
            if (container) {
                container.addEventListener('click', e => e.stopPropagation());
            }
        });

        // ESC key to close modals
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && modalOverlay.style.display === 'block') {
                closeAllModals();
            }
        });

        // Delete confirmation
        document.querySelectorAll('form[data-confirm="delete"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Apakah Anda yakin ingin menghapus arsip ini?')) {
                    e.preventDefault();
                }
            });
        });

        // Sortable headers hover effect
        document.querySelectorAll('.sortable-header').forEach(header => {
            header.addEventListener('mouseenter', () => header.style.backgroundColor = '#e9ecef');
            header.addEventListener('mouseleave', () => header.style.backgroundColor = '#f8f9fa');
        });

        // Tambahkan di bagian script yang sudah ada

// Update Status Bulk - SATU event listener SAJA
const updateStatusBtn = document.getElementById('updateStatusBtn');
if (updateStatusBtn) {
    updateStatusBtn.addEventListener('click', function() {
        // Konfirmasi dulu
        if (!confirm('Update status semua arsip berdasarkan tahun sekarang? Proses ini akan mengubah status arsip yang sudah memasuki masa HABIS_RETENSI.')) {
            return;
        }
        
        // Tampilkan modal loading
        const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
        modal.show();
        
        // Ubah tombol jadi loading
        const originalHtml = updateStatusBtn.innerHTML;
        updateStatusBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Mengupdate...';
        updateStatusBtn.disabled = true;
        
        // Kirim request
        fetch('{{ route("arsip.update-status-bulk") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            // TUTUP MODAL LOADING
            modal.hide();
            
            // KEMBALIKAN TOMBOL KE SEMULA
            updateStatusBtn.innerHTML = originalHtml;
            updateStatusBtn.disabled = false;
            
            if (data.success) {
                // Tampilkan notifikasi sukses
                showNotification('success', data.message);
                
                // Reload halaman setelah 1.5 detik
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // TUTUP MODAL LOADING
            modal.hide();
            
            // KEMBALIKAN TOMBOL KE SEMULA
            updateStatusBtn.innerHTML = originalHtml;
            updateStatusBtn.disabled = false;
            
            showNotification('error', 'Terjadi kesalahan saat update status: ' + error.message);
        });
    });
}

// Fungsi notifikasi
function showNotification(type, message) {
    // Hapus notifikasi yang sudah ada
    const existingAlert = document.querySelector('.alert-notification');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show alert-notification position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.style.maxWidth = '500px';
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} fs-5"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alertDiv);
    
    // Auto close setelah 3 detik
    setTimeout(() => {
        if (alertDiv) alertDiv.remove();
    }, 3000);
}

        // Auto-focus search
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) searchInput.focus();

        // Helper function to escape HTML (optional, but safe)
        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========== INLINE EDITING UNTUK TEXT BIASA ==========
    document.querySelectorAll('.editable').forEach(cell => {
        cell.addEventListener('dblclick', function(e) {
            const currentText = this.innerText.trim();
            const field = this.dataset.field;
            const rowId = this.closest('tr').dataset.id;
            
            const input = document.createElement('input');
            input.type = 'text';
            input.value = currentText === '-' ? '' : currentText;
            input.classList.add('form-control', 'form-control-sm');
            input.style.width = '100%';
            
            this.innerHTML = '';
            this.appendChild(input);
            input.focus();
            
            const save = () => {
                const newValue = input.value.trim();
                this.innerText = newValue === '' ? '-' : newValue;
                if (newValue !== currentText) {
                    sendUpdate(rowId, field, newValue, this);
                }
            };
            
            input.addEventListener('blur', save);
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') input.blur();
            });
        });
    });
    
    // ========== INLINE EDITING UNTUK DROPDOWN (LOKASI & JRA) ==========
    document.querySelectorAll('.editable-select').forEach(cell => {
        cell.addEventListener('dblclick', function(e) {
            const currentValue = this.dataset.value || '';
            const field = this.dataset.field;
            const rowId = this.closest('tr').dataset.id;
            const currentLabel = this.innerText.trim();
            
            // Tentukan pilihan dropdown berdasarkan field
            let options = [];
            if (field === 'lokasi_arsip') {
                options = [
                    { value: '', label: 'Pilih Lokasi' },
                    { value: 'RECORD_CENTER_PERMANEN', label: 'Record Center (Arsip Permanen)' },
                    { value: 'RECORD_CENTER_INAKTIF', label: 'Record Center (Arsip Inaktif)' }
                ];
            } else if (field === 'keterangan_jra') {
                options = [
                    { value: '', label: 'Pilih Keterangan' },
                    { value: 'PERMANEN', label: 'Permanen' },
                    { value: 'MUSNAH', label: 'Musnah' }
                ];
            } else {
                return; // Jika bukan kedua field, abaikan
            }
            
            // Buat elemen select
            const select = document.createElement('select');
            select.classList.add('form-select', 'form-select-sm');
            options.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.label;
                if (opt.value === currentValue) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            
            // Kosongkan cell dan tampilkan select
            this.innerHTML = '';
            this.appendChild(select);
            select.focus();
            
            const save = () => {
                const newValue = select.value;
                const selectedOption = select.options[select.selectedIndex];
                const newLabel = selectedOption ? selectedOption.textContent : '-';
                
                // Update tampilan cell dengan label yang dipilih
                this.innerText = newLabel;
                this.dataset.value = newValue;
                
                // Jika nilai berubah, kirim ke server
                if (newValue !== currentValue) {
                    sendUpdate(rowId, field, newValue, this);
                } else if (newValue === '' && currentValue !== '') {
                    // Jika memilih opsi kosong (Pilih Lokasi/Keterangan) maka kirim nilai kosong
                    sendUpdate(rowId, field, '', this);
                }
            };
            
            select.addEventListener('blur', save);
            select.addEventListener('change', () => select.blur());
        });
    });
    
    // ========== FUNGSI AJAX UNTUK UPDATE ==========
    function sendUpdate(id, field, value, cellElement) {
        fetch(`/arsip/${id}/inline-update`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ field: field, value: value })
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || `HTTP ${response.status}`);
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                const row = cellElement.closest('tr');
                if (data.new_values.aktif_sampai) {
                    row.querySelector('.aktif-sampai-cell').innerText = data.new_values.aktif_sampai;
                }
                if (data.new_values.inaktif_sampai) {
                    row.querySelector('.inaktif-sampai-cell').innerText = data.new_values.inaktif_sampai;
                }
                if (data.new_values.status_arsip) {
                    row.querySelector('.status-cell').innerHTML = data.new_values.status_arsip;
                }
                showNotification('✅ Data berhasil diupdate', 'success');
            } else {
                showNotification('❌ Gagal: ' + (data.error || 'Unknown error'), 'danger');
                // Kembalikan tampilan ke nilai semula
                location.reload();
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showNotification('❌ ' + error.message, 'danger');
            location.reload();
        });
    }
    
    // Notifikasi sementara
    function showNotification(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '250px';
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <span>${message}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 2000);
    }
});
</script>
@endsection