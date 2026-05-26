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
                  <a href="{{ route('subbagian.arsip.index', ['show_duplicates' => 1]) }}" class="btn btn-danger d-flex align-items-center gap-2" id="checkDuplicatesBtn">
                    <i class="bi bi-files"></i>
                    <span>Cek Duplikasi</span>
                </a>
                <button class="btn btn-warning d-flex align-items-center gap-2 shadow-sm" id="ajukanPindahBtn" style="display: none;">
                    <i class="bi bi-arrow-right-circle-fill"></i>
                    <span>Ajukan Pindah ke Unit Kearsipan</span>
                </button>
                <a href="{{ route('subbagian.arsip.create') }}" class="btn btn-orange d-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Tambah Baru</span>
                </a>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Selected Counter -->
        <div id="selectedCounter" class="alert alert-info d-flex align-items-center justify-content-between mb-3" style="display: none!important;">
            <div>
                <i class="bi bi-check-circle-fill me-2"></i>
                <span id="selectedCount">0</span> arsip dipilih
            </div>
            <button class="btn btn-sm btn-outline-danger" id="clearSelection">
                <i class="bi bi-x-circle"></i> Batalkan Pilihan
            </button>
        </div>

        <div class="alert alert-info border-0 shadow-sm mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-info-circle-fill fs-4 me-3 text-primary"></i>
        <div>
            <h6 class="fw-bold mb-1">Informasi Menu Arsip Internal</h6>
            <p class="mb-0">
                Menu Arsip Internal digunakan untuk mengelola seluruh arsip yang <b> dibuat oleh 
                KPU Provinsi Bali </b>, mulai dari surat dinas, berita acara, laporan, 
                dokumentasi kegiatan, hingga dokumen administrasi lainnya
            </p>
        </div>
    </div>
</div>
        
        <!-- Search Bar -->
        <form method="GET" action="{{ route('subbagian.arsip.index') }}" class="mb-4">
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
        @if(session('warning'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    {{ session('warning') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
    <a href="{{ route('subbagian.arsip.index', request()->except('show_duplicates')) }}" class="ms-auto btn btn-sm btn-outline-secondary">
        <i class="bi bi-x-lg"></i> Hapus filter
    </a>
</div>
@endif
        
        <!-- Table dengan horizontal scroll -->
        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="table table-hover" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAll">
                        </th>
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
                        <th style="min-width: 300px;" class="sortable-header">
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
                        <th style="min-width: 100px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'jumlah_berkas', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span>Jumlah</span>
                                @if(request('sort') == 'jumlah_berkas')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>
                        <th style="min-width: 100px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nomor_rak', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span style="text-align: center">Nama/No Rak</span>
                                @if(request('sort') == 'nomor_rak')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>
                        <th style="min-width: 100px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nomor_box', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span style="text-align: center">Nama/No Box</span>
                                @if(request('sort') == 'nomor_box')
                                    <i class="bi bi-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}-fill text-dark"></i>
                                @else
                                    <i class="bi bi-caret-up-down text-secondary"></i>
                                @endif
                            </a>
                        </th>
                         
                        <th style="min-width: 120px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'status_pindah', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span>Status Pindah</span>
                                @if(request('sort') == 'status_pindah')
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
                    <tr>
                        <td>
                            <input type="checkbox" class="arsip-checkbox" value="{{ $arsip->id }}">
                        </td>
                        <td>{{ $loop->iteration + ($arsips->currentPage() - 1) * $arsips->perPage() }}</td>
                        <td><strong>{{ $arsip->kodeKlasifikasi->kode ?? 'N/A' }}</strong></td>
                        <td>{{ Str::limit($arsip->uraian_arsip, 200) }}</td>
                        <td>{{ $arsip->tahun_arsip }}</td>
                        <td>{{ $arsip->jumlah_berkas }} {{ $arsip->satuan_arsip }}</td>
                        <td>{{ $arsip->nomor_rak ?? '-' }}</td>
                        <td>{{ $arsip->nomor_box ?? '-' }}</td>
                        <td>
                            @if($arsip->status_pindah)
                                @php
                                    $statusPindahColors = [
                                        'DIAJUKAN' => 'warning',
                                        'DITERIMA' => 'success',
                                        'DITOLAK' => 'danger',
                                        'SELESAI' => 'info'
                                    ];
                                    $color = $statusPindahColors[$arsip->status_pindah] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">
                                    {{ $arsip->status_pindah }}
                                </span>
                            @else
                                <span class="badge bg-secondary">Belum Diajukan</span>
                            @endif
                              <!-- Label DUPLIKAT -->
                                @if($arsip->is_duplicate == 1)
                                    <span class="badge bg-danger">DUPLIKAT</span>
                                @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" style="gap: 6px;">
                                <a href="{{ route('subbagian.arsip.show', $arsip->id) }}" class="btn btn-info" title="Detail" style="padding: 0.25rem 0.5rem;">
                                    <i class="bi bi-eye"></i>
                                </a> 
                                <a href="{{ route('subbagian.arsip.edit', $arsip->id) }}" class="btn btn-warning" title="Edit" style="padding: 0.25rem 0.5rem;">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <!-- Tombol Ajukan Pindah per Arsip -->
                                <!-- @if($arsip->sub_bagian_id == auth()->user()->sub_bagian_id && $arsip->status_pindah !== 'DIAJUKAN')
                                <form action="{{ route('subbagian.arsip.ajukanPindah', $arsip->id) }}" method="POST" enctype="multipart/form-data" class="d-inline ajukan-pindah-single">
                                    @csrf
                                    <input type="file" name="file_berita_acara" class="d-none file-input-single" accept=".pdf,.jpg,.jpeg,.png">
                                    <button type="button" class="btn btn-warning btn-ajukan-single" title="Ajukan Pindah" style="padding: 0.25rem 0.5rem;" data-id="{{ $arsip->id }}" data-judul="{{ $arsip->uraian_arsip }}">
                                        <i class="bi bi-arrow-right-circle"></i>
                                    </button>
                                </form>
                                @endif -->
                                <form action="{{ route('subbagian.arsip.destroy', $arsip->id) }}"
                                    method="POST"
                                    data-confirm="delete"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Hapus" style="padding: 0.25rem 0.5rem;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="bi bi-folder-x fa-2x text-muted mb-2"></i>
                            <p class="text-muted">Belum ada data arsip</p>
                            <a href="{{ route('subbagian.arsip.create') }}" class="btn btn-primary">
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

<!-- Modal Ajukan Pindah Multiple -->
<!-- Modal Ajukan Pindah -->
<div class="modal-container" id="ajukanPindahModalContainer" style="display: none;">
    <div class="modal-content-wrapper">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="bi bi-file-text me-2"></i>
                    Ajukan Pemindahan Arsip
                </h5>
                <button type="button" class="btn-close-modal" id="closeAjukanPindahModal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('subbagian.arsip.ajukanPindahMultiple') }}" enctype="multipart/form-data" id="ajukanPindahForm">
                @csrf
                <div class="modal-body p-4">
                   <select name="bap_id" class="form-select" required>
    <option value="">-- Pilih Berita Acara --</option>
    @foreach($bapOptions as $bap)
        <option value="{{ $bap->id }}">
            {{ $bap->nomor_bap }} - {{ $bap->tanggal_bap }}
        </option>
    @endforeach
</select>

                    <!-- Daftar arsip yang dipilih -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Arsip yang akan diajukan (<span id="selectedCountModal">0</span> arsip)</label>
                        <div id="selectedArsipList" class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                            <!-- akan diisi JavaScript -->
                        </div>
                    </div>

                    <!-- <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Perhatian:</strong> Arsip yang diajukan akan menunggu persetujuan Unit Kearsipan.
                    </div> -->
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" id="cancelAjukanPindah">Batal</button>
                    <button type="submit" class="btn btn-warning" style="margin-left:5px">
                        <i class="bi bi-send me-1"></i> Ajukan Pemindahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
            <form method="GET" action="{{ route('subbagian.arsip.index') }}" id="filterForm">
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
                        
                        {{-- <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Sub Bagian</label>
                            <select name="sub_bagian_id" class="form-select">
                                <option value="">Semua Sub Bagian</option>
                                @foreach($subBagianOptions as $subBagian)
                                <option value="{{ $subBagian->id }}" {{ request('sub_bagian_id') == $subBagian->id ? 'selected' : '' }}>
                                    {{ $subBagian->nama_sub_bagian }}
                                </option>
                                @endforeach
                            </select>
                        </div> --}}

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
                                    {{ $kode->kode }} - {{ Str::limit($kode->uraian, 30) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Status Pindah</label>
                            <select name="status_pindah" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="" {{ request('status_pindah') == '' ? 'selected' : '' }}>Belum Diajukan</option>
                                <option value="DIAJUKAN" {{ request('status_pindah') == 'DIAJUKAN' ? 'selected' : '' }}>Diajukan</option>
                                <option value="DITERIMA" {{ request('status_pindah') == 'DITERIMA' ? 'selected' : '' }}>Diterima</option>
                                <option value="DITOLAK" {{ request('status_pindah') == 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
                                <option value="SELESAI" {{ request('status_pindah') == 'SELESAI' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div> -->
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
            <form action="{{ route('subbagian.arsip.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
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
                            <a href="{{ asset('template/template_daftar_arsip_subbag.xlsx') }}" 
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

<!-- Export excel Modal -->
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

          <form method="POST" action="{{ route('subbagian.arsip.export') }}">
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
<input type="hidden" name="sub_bagian_id" value="{{ auth()->user()->sub_bagian_id }}">
                <div class="modal-body">
                    <div class="row">
                        @php
                        $columns = [
                            'kode_klasifikasi' => 'Kode Klasifikasi',
                            'uraian_arsip' => 'Judul Arsip',
                            'tahun_arsip' => 'Tahun',
                            'jumlah_berkas' => 'Jumlah',
                            'sub_bagian' => 'Sub Bagian',
                            'keterangan' => 'Kondisi Fisik',
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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


</style>

<!-- Bagian JavaScript tetap sama seperti sebelumnya -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const modalOverlay = document.getElementById('modalOverlay');
    const filterModalContainer = document.getElementById('filterModalContainer');
    const importModalContainer = document.getElementById('importModalContainer');
    const exportModalContainer = document.getElementById('exportModalContainer');
    const ajukanPindahModal = document.getElementById('ajukanPindahModalContainer');
    
    const openFilterBtn = document.getElementById('openFilterModal');
    const openImportBtn = document.getElementById('openImportModal');
    const openExportBtn = document.getElementById('openExportModal');
    const ajukanPindahBtn = document.getElementById('ajukanPindahBtn');
    
    const closeFilterBtn = document.getElementById('closeFilterModal');
    const closeImportBtn = document.getElementById('closeImportModal');
    const closeExportBtn = document.getElementById('closeExportModal');
    const closeAjukanPindahBtn = document.getElementById('closeAjukanPindahModal');
    
    const cancelImportBtn = document.getElementById('cancelImport');
    const cancelExportBtn = document.getElementById('cancelExport');
    const cancelAjukanPindahBtn = document.getElementById('cancelAjukanPindah');
    
    const resetFilterBtn = document.getElementById('resetFilter');
    const clearSelectionBtn = document.getElementById('clearSelection');
    const selectAllCheckbox = document.getElementById('selectAll');
    const arsipCheckboxes = document.querySelectorAll('.arsip-checkbox');
    const selectedCounter = document.getElementById('selectedCounter');
    const selectedCount = document.getElementById('selectedCount');
    const selectedCountModal = document.getElementById('selectedCountModal');
    const selectedArsipList = document.getElementById('selectedArsipList');
    
    const filterForm = document.getElementById('filterForm');
    const importForm = document.getElementById('importForm');
    const ajukanPindahForm = document.getElementById('ajukanPindahForm');
    const excelFileInput = document.getElementById('excelFile');
    const fileInfo = document.getElementById('fileInfo');
    const submitImportBtn = document.getElementById('submitImport');
    
    const checkAll = document.getElementById('checkAllColumns');
    const columnChecks = document.querySelectorAll('.column-check');
    
    // Tombol ajukan pindah per arsip
    const ajukanSingleButtons = document.querySelectorAll('.btn-ajukan-single');
    
    // Selection Management
    let selectedArsips = new Set();
    
    // Fungsi untuk update selection UI
    function updateSelectionUI() {
        const count = selectedArsips.size;
        selectedCount.textContent = count;
        selectedCountModal.textContent = count;
        
        if (count > 0) {
            selectedCounter.style.display = 'flex!important';
            ajukanPindahBtn.style.display = 'flex';
            
            // Update modal list
            updateArsipListInModal();
        } else {
            selectedCounter.style.display = 'none!important';
            ajukanPindahBtn.style.display = 'none';
            selectedArsipList.innerHTML = '';
        }
        
        // Update select all checkbox
        selectAllCheckbox.checked = count === arsipCheckboxes.length;
    }
    
    // Fungsi untuk update daftar arsip di modal
    function updateArsipListInModal() {
        selectedArsipList.innerHTML = '';
        
        selectedArsips.forEach(id => {
            const checkbox = document.querySelector(`.arsip-checkbox[value="${id}"]`);
            if (checkbox) {
                const row = checkbox.closest('tr');
                const kode = row.cells[2].textContent.trim();
                const judul = row.cells[3].textContent.trim();
                const tahun = row.cells[4].textContent.trim();
                
                const item = document.createElement('div');
                item.className = 'mb-2 p-2 border rounded bg-white';
                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${kode}</strong>
                            <div class="small text-muted">${judul}</div>
                            <div class="small">Tahun: ${tahun}</div>
                        </div>
                        <input type="hidden" name="arsip_ids[]" value="${id}">
                    </div>
                `;
                selectedArsipList.appendChild(item);
            }
        });
    }
    
    // Event listener untuk checkbox individual
    arsipCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                selectedArsips.add(this.value);
            } else {
                selectedArsips.delete(this.value);
            }
            updateSelectionUI();
        });
    });
    
    // Event listener untuk select all
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            arsipCheckboxes.forEach(cb => {
                cb.checked = this.checked;
                if (this.checked) {
                    selectedArsips.add(cb.value);
                } else {
                    selectedArsips.delete(cb.value);
                }
            });
            updateSelectionUI();
        });
    }
    
    // Event listener untuk clear selection
    if (clearSelectionBtn) {
        clearSelectionBtn.addEventListener('click', function() {
            arsipCheckboxes.forEach(cb => {
                cb.checked = false;
            });
            selectedArsips.clear();
            updateSelectionUI();
        });
    }
    
    // Fungsi untuk membuka modal
    function openModal(modalContainer) {
        // Sembunyikan semua modal terlebih dahulu
        filterModalContainer.style.display = 'none';
        importModalContainer.style.display = 'none';
        exportModalContainer.style.display = 'none';
        ajukanPindahModal.style.display = 'none';
        
        // Tampilkan overlay
        modalOverlay.style.display = 'block';
        
        // Tampilkan modal yang dipilih
        modalContainer.style.display = 'flex';
        modalContainer.classList.add('active');
        
        // Tambahkan class untuk mencegah scroll body
        document.body.classList.add('modal-open');
        
        // Focus ke elemen pertama dalam modal
        setTimeout(() => {
            const firstInput = modalContainer.querySelector('input, select, textarea');
            if (firstInput) firstInput.focus();
        }, 100);
    }
    
    // Fungsi untuk menutup modal
    function closeModal() {
        modalOverlay.style.display = 'none';
        filterModalContainer.style.display = 'none';
        importModalContainer.style.display = 'none';
        exportModalContainer.style.display = 'none';
        ajukanPindahModal.style.display = 'none';
        
        filterModalContainer.classList.remove('active');
        importModalContainer.classList.remove('active');
        exportModalContainer.classList.remove('active');
        ajukanPindahModal.classList.remove('active');
        
        document.body.classList.remove('modal-open');
        
        // Reset form import
        if (fileInfo) fileInfo.innerHTML = '';
        if (excelFileInput) excelFileInput.value = '';
        if (submitImportBtn) {
            submitImportBtn.disabled = false;
            submitImportBtn.innerHTML = '<i class="bi bi-upload me-1"></i> Import Sekarang';
        }
    }
    
    // Event listeners untuk tombol buka modal
    if (openFilterBtn) {
        openFilterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(filterModalContainer);
        });
    }
    
    if (openImportBtn) {
        openImportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(importModalContainer);
        });
    }
    
    if (openExportBtn) {
        openExportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(exportModalContainer);
        });
    }
    
    // Event listener untuk tombol ajukan pindah multiple
    if (ajukanPindahBtn) {
        ajukanPindahBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(ajukanPindahModal);
        });
    }
    
    // Event listeners untuk tombol tutup modal
    if (closeFilterBtn) {
        closeFilterBtn.addEventListener('click', closeModal);
    }
    
    if (closeImportBtn) {
        closeImportBtn.addEventListener('click', closeModal);
    }
    
    if (closeExportBtn) {
        closeExportBtn.addEventListener('click', closeModal);
    }
    
    if (closeAjukanPindahBtn) {
        closeAjukanPindahBtn.addEventListener('click', closeModal);
    }
    
    if (cancelImportBtn) {
        cancelImportBtn.addEventListener('click', closeModal);
    }
    
    if (cancelExportBtn) {
        cancelExportBtn.addEventListener('click', closeModal);
    }
    
    if (cancelAjukanPindahBtn) {
        cancelAjukanPindahBtn.addEventListener('click', closeModal);
    }
    
    // Tutup modal saat klik overlay
    if (modalOverlay) {
        modalOverlay.addEventListener('click', closeModal);
    }
    
    // Mencegah modal tertutup saat klik di dalam modal
    [filterModalContainer, importModalContainer, exportModalContainer, ajukanPindahModal].forEach(modal => {
        if (modal) {
            modal.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });
    
    // Reset filter
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
            window.location.href = "{{ route('subbagian.arsip.index') }}";
        });
    }
    
    // File upload preview untuk import
    if (excelFileInput) {
        excelFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                const fileName = file.name;
                const fileExtension = fileName.split('.').pop().toLowerCase();
                
                // Validasi ekstensi
                const allowedExtensions = ['xlsx', 'xls'];
                if (!allowedExtensions.includes(fileExtension)) {
                    fileInfo.innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            Format file tidak didukung. Hanya file Excel (.xlsx, .xls) yang diperbolehkan.
                            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    excelFileInput.value = '';
                    if (submitImportBtn) submitImportBtn.disabled = true;
                    return;
                }
                
                // Validasi ukuran file
                const maxSize = 5;
                if (fileSize > maxSize) {
                    fileInfo.innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            Ukuran file terlalu besar (${fileSize} MB). Maksimal ${maxSize}MB.
                            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    excelFileInput.value = '';
                    if (submitImportBtn) submitImportBtn.disabled = true;
                    return;
                }
                
                // Tampilkan info file valid
                fileInfo.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        File <strong>${fileName}</strong> (${fileSize} MB) siap diimport.
                        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                    </div>
                `;
                if (submitImportBtn) submitImportBtn.disabled = false;
            }
        });
    }
    
    // Validasi form import
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            const fileInput = this.querySelector('input[type="file"]');
            if (!fileInput || !fileInput.files.length) {
                e.preventDefault();
                alert('Silakan pilih file Excel terlebih dahulu.');
                return;
            }
            
            // Tampilkan loading
            if (submitImportBtn) {
                submitImportBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Mengimport...
                `;
                submitImportBtn.disabled = true;
            }
        });
    }
    
    // Check all columns untuk export
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            columnChecks.forEach(cb => cb.checked = this.checked);
        });
    }
    
    // Validasi form ajukan pindah multiple
    if (ajukanPindahForm) {
        ajukanPindahForm.addEventListener('submit', function(e) {
            if (selectedArsips.size === 0) {
                e.preventDefault();
                alert('Pilih setidaknya satu arsip untuk diajukan pemindahan.');
                return;
            }
            
            // Tampilkan loading
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Mengajukan...
            `;
            submitBtn.disabled = true;
        });
    }
    
    // Handle ajukan pindah per arsip (single)
    ajukanSingleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const arsipId = this.getAttribute('data-id');
            const arsipJudul = this.getAttribute('data-judul');
            
            // Buat input file sementara
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.pdf,.jpg,.jpeg,.png';
            input.style.display = 'none';
            
            input.addEventListener('change', function(e) {
                if (this.files.length > 0) {
                    // Konfirmasi sebelum submit
                    if (confirm(`Ajukan pemindahan arsip: ${arsipJudul}?`)) {
                        // Cari form yang sesuai
                        const form = button.closest('form');
                        const fileInput = form.querySelector('.file-input-single');
                        
                        // Buat DataTransfer untuk menyalin file
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(this.files[0]);
                        fileInput.files = dataTransfer.files;
                        
                        // Submit form
                        form.submit();
                    }
                }
            });
            
            // Trigger click pada input file
            document.body.appendChild(input);
            input.click();
            document.body.removeChild(input);
        });
    });
    
    // Tutup modal dengan tombol ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalOverlay.style.display === 'block') {
            closeModal();
        }
    });
    
    // Handle delete confirmation
    const deleteForms = document.querySelectorAll('form[data-confirm="delete"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus arsip ini?')) {
                e.preventDefault();
            }
        });
    });
    
    // Sortable headers hover effect
    const sortableHeaders = document.querySelectorAll('.sortable-header');
    sortableHeaders.forEach(header => {
        header.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#e9ecef';
        });
        
        header.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
    });
    
    // Auto-focus pada input pencarian
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.focus();
    }
    
    // Inisialisasi selection UI
    updateSelectionUI();
    // Jika salah satu kolom di-uncheck → "Pilih Semua" ikut uncheck
columnChecks.forEach(cb => {
    cb.addEventListener('change', function() {
        const allChecked = [...columnChecks].every(c => c.checked);
        checkAll.checked = allChecked;
    });
});
});
</script>
@endsection