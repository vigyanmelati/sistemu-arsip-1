@extends('layouts.app')

@section('page-title', 'Daftar Arsip Masuk')
@section('page-subtitle', 'Verifikasi Arsip yang Diajukan Subbagian')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Arsip Masuk</h5>
            <div class="action-buttons d-flex gap-2 flex-wrap">
                <button class="btn btn-cyan d-flex align-items-center gap-2" id="openFilterModal">
                    <i class="bi bi-funnel-fill"></i>
                    <span>Filter</span>
                </button>
                <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" id="openImportModal">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    <span>Import</span>
                </button>
                <button class="btn btn-success d-flex align-items-center gap-2 shadow-sm" id="openExportModal">
                    <i class="bi bi-file-earmark-arrow-down-fill"></i>
                    <span>Export</span>
                </button>
                @if($filterDuplikat)
                    <a href="{{ route('arsip-masuk.index') }}" class="btn btn-secondary d-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-x-circle"></i>
                        <span>Keluar Mode Duplikat</span>
                    </a>
                @else
                    <a href="{{ route('arsip-masuk.index', ['filter_duplikat' => 1]) }}" class="btn btn-danger d-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-search"></i>
                        <span>Cek Duplikat</span>
                    </a>
                @endif
                <button class="btn btn-warning d-flex align-items-center gap-2 shadow-sm" id="prosesMultipleBtn" style="display: none;">
                    <i class="bi bi-gear-fill"></i>
                    <span>Proses Multiple</span>
                </button>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Selected Counter -->
        <!-- <div id="selectedCounter" class="alert alert-info d-flex align-items-center justify-content-between mb-3" style="display: none!important;">
            <div>
                <i class="bi bi-check-circle-fill me-2"></i>
                <span id="selectedCount">0</span> arsip dipilih
            </div>
            <button class="btn btn-sm btn-outline-danger" id="clearSelection">
                <i class="bi bi-x-circle"></i> Batalkan Pilihan
            </button>
        </div> -->
        
        <!-- Search Bar -->
        <form method="GET" action="{{ route('arsip-masuk.index') }}" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan judul, kode, atau sub bagian..." value="{{ request('search') }}">
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
        @if($filterDuplikat)
<div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div>Mode <strong>Cek Duplikat</strong> aktif — hanya menampilkan arsip berstatus DIAJUKAN yang punya kode klasifikasi, sub bagian, tahun, dan judul yang sama.</div>
</div>
@endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
      @if(session('import_failed'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>
        <i class="bi bi-x-circle-fill me-2"></i>
        Import dibatalkan karena terdapat kesalahan:
    </strong>
    <ul class="mb-0 mt-2">
        @foreach(session('import_failed') as $row)
            <li>Baris {{ $row['baris'] }}: {{ $row['pesan'] }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
        <div class="alert alert-info d-flex align-items-start gap-2 mb-3">
    <i class="bi bi-info-circle-fill mt-1"></i>
    <div>
        <strong>Tips:</strong> Klik pada nilai di kolom
        <span class="badge bg-primary">Kode Klasifikasi</span>,
        <span class="badge bg-primary">Aktif Tahun</span>,
        <span class="badge bg-primary">Inaktif Tahun</span>, dan
        <span class="badge bg-primary">Keterangan JRA</span>
        untuk mengubah nilainya secara langsung (inline edit) tanpa perlu membuka halaman lain.
    </div>
</div>

<!-- Table -->
<div class="table-responsive">
        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover" id="arsipTable">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                        <th>#</th>
                        <th>Kode Klasifikasi</th>
                        <th>Judul Arsip</th>
                        <th>Sub Bagian</th>
                        <th>Tahun</th>
                        <th>Jumlah</th>
                        <th>No Rak</th>
                        <th>No Box</th>
                        <th>Aktif Tahun</th>
                        <th>Inaktif Tahun</th>
                        <th>Keterangan JRA</th>
                        <th>Aktif Sampai</th>
                        <th>Inaktif Sampai</th>
                        <th>Status</th>
                        <th>Tanggal Diajukan</th>
                        <th>File Berita Acara</th>
                        <th>Dokumen Arsip</th>
                        <th>Status Pemindahan</th>
                        <!-- <th>Status Lokasi</th> -->
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arsips as $arsip)
                    <tr data-id="{{ $arsip->id }}">
                        <td><input type="checkbox" class="arsip-checkbox" value="{{ $arsip->id }}"></td>
                        <td>{{ $loop->iteration + ($arsips->currentPage() - 1) * $arsips->perPage() }}</td>
                        <td>
                            <span class="editable-field editable-click" data-field="kode_klasifikasi" data-id="{{ $arsip->id }}" data-type="select" data-url="{{ route('arsip-masuk.update-field', $arsip->id) }}">
                                {{ $arsip->kodeKlasifikasi->kode ?? 'N/A' }}
                            </span>
                        </td>
                        <td>{{ Str::limit($arsip->uraian_arsip, 150) }}</td>
                        <td>{{ $arsip->subBagian->nama_sub_bagian ?? 'N/A' }}</td>
                        <td>{{ $arsip->tahun_arsip }}</td>
                        <td>{{ $arsip->jumlah_berkas }} {{ $arsip->satuan_arsip }}</td>
                        <td>{{ $arsip->rak ? $arsip->rak->nomor_rak : '-' }}</td>
                        <td>{{ $arsip->box ? $arsip->box->nomor_box : '-' }}</td>
                        <td>
                            <span class="editable-field editable-click" data-field="aktif_tahun" data-id="{{ $arsip->id }}" data-type="text" data-url="{{ route('arsip-masuk.update-field', $arsip->id) }}">
                                {{ $arsip->aktif_tahun ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="editable-field editable-click" data-field="inaktif_tahun" data-id="{{ $arsip->id }}" data-type="text" data-url="{{ route('arsip-masuk.update-field', $arsip->id) }}">
                                {{ $arsip->inaktif_tahun ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="editable-field editable-click" data-field="keterangan_jra" data-id="{{ $arsip->id }}" data-type="select" data-url="{{ route('arsip-masuk.update-field', $arsip->id) }}">
                                {{ $arsip->keterangan_jra ?? '-' }}
                            </span>
                        </td>
                       {{-- Kolom Aktif Sampai --}}
<td>
    <span class="editable-field" data-field="aktif_sampai" data-id="{{ $arsip->id }}">
        {{ $arsip->aktif_sampai ? \Carbon\Carbon::parse($arsip->aktif_sampai)->format('d/m/Y') : '-' }}
    </span>
</td>

{{-- Kolom Inaktif Sampai --}}
<td>
    <span class="editable-field" data-field="inaktif_sampai" data-id="{{ $arsip->id }}">
        {{ $arsip->inaktif_sampai ? \Carbon\Carbon::parse($arsip->inaktif_sampai)->format('d/m/Y') : '-' }}
    </span>
</td>
                        <td>
                           <span class="editable-field" data-field="status" data-id="{{ $arsip->id }}">
    @if($arsip->status_arsip)
        @php
            $statusColors = ['AKTIF' => 'success', 'INAKTIF' => 'warning', 'PERMANEN' => 'info', 'MUSNAH' => 'danger', 'HABIS_RETENSI' => 'secondary'];
            $color = $statusColors[$arsip->status_arsip] ?? 'secondary';
        @endphp
        <span class="badge bg-{{ $color }}">{{ $arsip->status_arsip }}</span>
    @else
                                @endif
                            </span>
                        </td>
                        <td>{{ $arsip->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($arsip->file_berita_acara)
                                <a href="{{ route('arsip-masuk.download-berita-acara', $arsip->id) }}" class="btn btn-sm btn-success"><i class="bi bi-download"></i> Download</a>
                            @else
                                <span class="badge bg-warning">Tidak ada</span>
                            @endif
                        </td>
                        <td>
                            @if($arsip->file_dokumen)
                                <a href="{{ route('arsip-masuk.download-file', $arsip->id) }}" class="btn btn-sm btn-primary" title="Download dokumen">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </a>
                            @elseif($arsip->link_folder)
                                <a href="{{ $arsip->link_folder }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Buka link dokumen">
                                    <i class="bi bi-link-45deg"></i> Link
                                </a>
                            @else
                                <span class="badge bg-danger" title="Arsip ini belum memiliki dokumen (PDF/link)">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Belum Ada Dokumen
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($arsip->status_pindah)
                                @php
                                    $statusPindahColors = ['DIAJUKAN' => 'warning','DITERIMA' => 'success','DITOLAK' => 'danger','SELESAI' => 'info'];
                                    $color = $statusPindahColors[$arsip->status_pindah] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ $arsip->status_pindah }}</span>
                            @else
                                <span class="badge bg-secondary">Belum Diajukan</span>
                            @endif
                        </td>
                        <!-- <td>
                            @if($arsip->tanggal_diverifikasi)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Sudah Diverifikasi
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-exclamation-circle"></i> Belum Diverifikasi
                                </span>
                            @endif
                        </td> -->
                       <td class="d-flex gap-1">
    <a href="{{ route('arsip-masuk.show', $arsip->id) }}" class="btn btn-sm btn-info" title="Detail">
        <i class="bi bi-eye"></i>
    </a>

    @if($filterDuplikat)
        <form action="{{ route('arsip-masuk.tandai-non-arsip', $arsip->id) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Tandai arsip ini sebagai Non Arsip?');">
            @csrf
            <button type="submit" class="btn btn-sm btn-warning" title="Tandai Non Arsip">
                <i class="bi bi-slash-circle"></i>
            </button>
        </form>

        <form action="{{ route('arsip-masuk.hapus-duplikat', $arsip->id) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Hapus arsip duplikat ini secara permanen?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    @endif
</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="20" class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted mb-2"></i>
                            <p class="text-muted">Tidak ada arsip masuk yang perlu diverifikasi</p>
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
            <nav aria-label="Page navigation">
                {{ $arsips->withQueryString()->links('pagination::bootstrap-5')->with('class', 'pagination pagination-sm mb-0') }}
            </nav>
        </div>
    </div>
</div>

<!-- MODAL OVERLAY -->
<div class="modal-overlay" id="modalOverlay" style="display: none;"></div>

<!-- Filter Modal -->
<div class="modal-container" id="filterModalContainer" style="display: none;">
    <div class="modal-content-wrapper">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-funnel me-2"></i> Filter Arsip Masuk
                </h5>
                <button type="button" class="btn-close-modal" id="closeFilterModal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form method="GET" action="{{ route('arsip-masuk.index') }}" id="filterForm">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Sub Bagian</label>
                            <select name="sub_bagian_id" class="form-select">
                                <option value="">Semua Sub Bagian</option>
                                @foreach($subBagianOptions as $subBagian)
                                <option value="{{ $subBagian->id }}" {{ request('sub_bagian_id') == $subBagian->id ? 'selected' : '' }}>
                                    {{ $subBagian->nama_sub_bagian }}
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
                            <label class="form-label fw-semibold">Status Arsip</label>
                            <select name="status_arsip" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="AKTIF" {{ request('status_arsip') == 'AKTIF' ? 'selected' : '' }}>Aktif</option>
                                <option value="INAKTIF" {{ request('status_arsip') == 'INAKTIF' ? 'selected' : '' }}>Inaktif</option>
                                <option value="PERMANEN" {{ request('status_arsip') == 'PERMANEN' ? 'selected' : '' }}>Permanen</option>
                                <option value="MUSNAH" {{ request('status_arsip') == 'MUSNAH' ? 'selected' : '' }}>Musnah</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Nomor BAP</label>
                            <select name="nomor_bap" class="form-select">
                                <option value="">Semua Nomor BAP</option>
                                @foreach($nomorBapOptions as $nomorBap)
                                    <option value="{{ $nomorBap }}" {{ request('nomor_bap') == $nomorBap ? 'selected' : '' }}>
                                        {{ $nomorBap }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Status Dokumen Arsip</label>
                            <select name="status_dokumen" class="form-select">
                                <option value="">Semua</option>
                                <option value="belum" {{ request('status_dokumen') == 'belum' ? 'selected' : '' }}>
                                    ⚠️ Belum Ada Dokumen
                                </option>
                                <option value="sudah" {{ request('status_dokumen') == 'sudah' ? 'selected' : '' }}>
                                    ✅ Sudah Ada Dokumen
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" id="resetFilter">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset Filter
                    </button>
                    <button type="submit" class="btn btn-primary" style="margin-left:10px">
                        <i class="bi bi-check-circle me-1"></i> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Proses Multiple Modal -->
<!-- Proses Multiple Modal -->
<div class="modal-container" id="prosesMultipleModalContainer" style="display: none;">
    <div class="modal-content-wrapper">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="bi bi-gear-fill me-2"></i>
                    Proses Multiple Arsip
                </h5>
                <button type="button" class="btn-close-modal" id="closeProsesMultipleModal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.arsip-masuk.proses-multiple') }}" id="prosesMultipleForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Aksi <span class="text-danger">*</span></label>
                        <select name="action" class="form-select" id="multipleActionSelect" required>
                            <option value="">Pilih Aksi</option>
                            <option value="setujui">✅ Setujui Arsip</option>
                            <option value="tolak">❌ Tolak Arsip</option>
                        </select>
                    </div>

                    <!-- Fields for Setujui -->
                    <div id="setujuiFields" style="display: none;">
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Arsip yang dipilih akan disetujui dan langsung dipindahkan ke Unit Kearsipan.
                        </div>

                        <!-- <div class="mb-3">
                            <label class="form-label fw-semibold">Status Arsip Setelah Pindah <span class="text-danger">*</span></label>
                            <select name="status_arsip_setelah_pindah" id="status_arsip_multiple" class="form-select">
                                <option value="">-- Pilih Status --</option>
                                <option value="AKTIF">Aktif</option>
                                <option value="INAKTIF">Inaktif</option>
                                <option value="PERMANEN">Permanen</option>
                                <option value="MUSNAH">Musnah</option>
                            </select>
                        </div> -->

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lokasi Tujuan <span class="text-danger">*</span></label>
                            <select name="lokasi_tujuan" id="lokasi_tujuan_multiple" class="form-select">
                                <option value="">-- Pilih Lokasi --</option>
                                <option value="RECORD_CENTER_PERMANEN">Record Center Permanen</option>
                                <option value="RECORD_CENTER_INAKTIF">Record Center Inaktif</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rak Tujuan <span class="text-danger">*</span></label>
                            <select name="rak_id" id="rak_id_multiple" class="form-select">
                                <option value="">-- Pilih Lokasi Terlebih Dahulu --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Box Tujuan <span class="text-danger">*</span></label>
                            <select name="box_id" id="box_id_multiple" class="form-select">
                                <option value="">-- Pilih Rak Terlebih Dahulu --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan (Opsional)</label>
                            <textarea name="catatan" class="form-control" rows="2"
                                    placeholder="Catatan persetujuan & pemindahan arsip..."></textarea>
                        </div>
                    </div>

                    <!-- Fields for Tolak -->
                    <div id="tolakFields" style="display: none;">
                        <div class="alert alert-danger mb-3">
                            <i class="bi bi-x-circle-fill me-2"></i>
                            Arsip yang dipilih akan ditolak dan dikembalikan ke Sub Bagian untuk diperbaiki.
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="alasan" id="alasan_multiple" class="form-control" rows="3"
                                    placeholder="Jelaskan alasan penolakan untuk semua arsip yang dipilih..."></textarea>
                            <small class="text-muted">Alasan ini akan dikirim ke Sub Bagian terkait untuk setiap arsip.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Arsip yang akan diproses (<span id="selectedCountModal">0</span> arsip)</label>
                        <div id="selectedArsipList" class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                            <!-- Daftar arsip yang dipilih akan muncul di sini -->
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Perhatian:</strong> Aksi ini akan memproses semua arsip yang dipilih sekaligus.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" id="cancelProsesMultiple">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-warning" style="margin-left:10px">
                        <i class="bi bi-gear-fill me-1"></i> Proses Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>>


</div>
<!-- Import Modal -->
<div class="modal-container" id="importModalContainer" style="display: none;">
    <div class="modal-content-wrapper">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-arrow-up-fill me-2"></i> Import Arsip dari Excel
                </h5>
                <button type="button" class="btn-close-modal" id="closeImportModal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('arsip-masuk.import') }}" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Gunakan template resmi agar kolom terbaca dengan benar. Kolom
                        <strong>Nomor Berita Acara</strong>, <strong>Kode Klasifikasi</strong>,
                        <strong>Sub Bagian</strong>, <strong>Judul Arsip</strong>, dan
                        <strong>Tahun Arsip</strong> wajib diisi. Jika arsip sudah ada
                        (dicocokkan otomatis), data retensi (Aktif/Inaktif Tahun,
                        Keterangan JRA) akan diperbarui. Jika belum ada, arsip baru akan
                        dibuat dengan asal data <strong>"Import Excel"</strong>.
                    </div>

                    <a href="{{ route('arsip-masuk.template-import') }}" class="btn btn-sm btn-outline-secondary mb-3">
                        <i class="bi bi-download me-1"></i> Download Template Excel
                    </a>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">File Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file_import" id="file_import" class="form-control" accept=".xlsx,.xls" required>
                        <small class="text-muted">Format .xlsx atau .xls, maksimal 5MB.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" id="cancelImport">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitImport" style="margin-left:10px">
                        <i class="bi bi-upload me-1"></i> Proses Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Export Modal -->
<div class="modal-container" id="exportModalContainer" style="display: none;">
    <div class="modal-content-wrapper">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-arrow-down-fill me-2"></i> Export Arsip Masuk
                </h5>
                <button type="button" class="btn-close-modal" id="closeExportModal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form method="GET" action="{{ route('arsip-masuk.export') }}" id="exportForm">
                <div class="modal-body p-4">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Pilih Nomor Berita Acara yang ingin di-export, atau biarkan
                        <strong>"Semua Nomor BAP"</strong> untuk export seluruh arsip masuk
                        (mengikuti filter yang sedang aktif di tabel, kalau ada).
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomor Berita Acara</label>
                        <select name="nomor_bap" class="form-select">
                            <option value="">Semua Nomor BAP</option>
                            @foreach($nomorBapOptions as $nomorBap)
                                <option value="{{ $nomorBap }}" {{ request('nomor_bap') == $nomorBap ? 'selected' : '' }}>
                                    {{ $nomorBap }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Bawa serta filter tabel yang sedang aktif supaya ikut ter-export --}}
                    <input type="hidden" name="sub_bagian_id" value="{{ request('sub_bagian_id') }}">
                    <input type="hidden" name="tahun_arsip" value="{{ request('tahun_arsip') }}">
                    <input type="hidden" name="status_arsip" value="{{ request('status_arsip') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" id="cancelExport">Batal</button>
                    <button type="submit" class="btn btn-success" style="margin-left:10px">
                        <i class="bi bi-download me-1"></i> Export Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Cek Duplikat Modal -->
<div class="modal-container" id="cekDuplikatModalContainer" style="display: none;">
    <div class="modal-content-wrapper" style="width: 800px;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-search me-2"></i> Cek Duplikat Arsip
                </h5>
                <button type="button" class="btn-close-modal" id="closeCekDuplikatModal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body p-4" id="cekDuplikatBody" style="max-height: 60vh; overflow-y: auto;">
                <div class="text-center py-4">
                    <div class="spinner-border text-danger" role="status"></div>
                    <p class="mt-2 text-muted">Memeriksa data duplikat...</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" id="closeCekDuplikatFooter">Tutup</button>
            </div>
        </div>
    </div>
</div>
<style>
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
    
    .modal-content-wrapper {
        max-width: 90%;
        width: 600px;
        animation: modalSlideIn 0.3s ease-out;
        pointer-events: all;
    }
    
    .modal-content {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .modal-header {
        padding: 1.2rem 1.5rem;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        position: relative;
    }
    
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
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-footer {
        padding: 1.2rem 1.5rem;
        border-top: 1px solid #dee2e6;
        background: #f8f9fa;
    }
    
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

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    /* Editable field hover effect */
    .editable-field {
        padding: 2px 4px;
        border-radius: 3px;
        transition: all 0.2s;
        display: inline-block;
        width: 100%;
        min-height: 24px;
    }
    
    .editable-click {
        cursor: pointer;
    }
    
    .editable-click:hover {
        background-color: #e3f2fd;
        text-decoration: underline;
        color: #0d6efd;
    }
    
    /* Inline edit input styles */
    .inline-edit-input {
        width: 100%;
        padding: 2px 6px;
        border: 2px solid #0d6efd;
        border-radius: 4px;
        font-size: 0.9rem;
        background: white;
        min-height: 30px;
    }
    
    .inline-edit-input:focus {
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .inline-edit-select {
        width: 100%;
        padding: 2px 6px;
        border: 2px solid #0d6efd;
        border-radius: 4px;
        font-size: 0.9rem;
        background: white;
        min-height: 30px;
    }
    
    .inline-edit-select:focus {
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .editing {
        background-color: #fff3cd !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const modalOverlay = document.getElementById('modalOverlay');
    const filterModalContainer = document.getElementById('filterModalContainer');
    const prosesMultipleModalContainer = document.getElementById('prosesMultipleModalContainer');
    const importModalContainer = document.getElementById('importModalContainer');
    const cekDuplikatModalContainer = document.getElementById('cekDuplikatModalContainer');
    const exportModalContainer = document.getElementById('exportModalContainer');
    const openFilterBtn = document.getElementById('openFilterModal');
    const prosesMultipleBtn = document.getElementById('prosesMultipleBtn');
    const closeFilterBtn = document.getElementById('closeFilterModal');
    const closeProsesMultipleBtn = document.getElementById('closeProsesMultipleModal');
    const cancelProsesMultipleBtn = document.getElementById('cancelProsesMultiple');
    const resetFilterBtn = document.getElementById('resetFilter');
    
    const selectAllCheckbox = document.getElementById('selectAll');
    const arsipCheckboxes = document.querySelectorAll('.arsip-checkbox');
    const selectedCounter = document.getElementById('selectedCounter');
    const selectedCount = document.getElementById('selectedCount');
    const selectedCountModal = document.getElementById('selectedCountModal');
    const selectedArsipList = document.getElementById('selectedArsipList');
    
    const multipleActionSelect = document.getElementById('multipleActionSelect');
    const setujuiFields = document.getElementById('setujuiFields');
    const tolakFields = document.getElementById('tolakFields');
    
    const prosesMultipleForm = document.getElementById('prosesMultipleForm');
    
    
    // Elements untuk filter rak & box
    const lokasiTujuanMultiple = document.getElementById('lokasi_tujuan_multiple');
    const rakSelectMultiple = document.getElementById('rak_id_multiple');
    const boxSelectMultiple = document.getElementById('box_id_multiple');
    
    // Data rak dan box dari server
    const rakData = @json($rakOptions ?? []);
    const boxData = @json($boxOptions ?? []);
    
    // Data untuk dropdown options
    const kodeKlasifikasiOptions = @json($kodeKlasifikasiOptions ?? []);
    const keteranganJraOptions = ['Musnah', 'Permanen'];
    
    // Selection Management
    let selectedArsips = new Set();
    
    // Fungsi untuk update selection UI
    function updateSelectionUI() {
    const count = selectedArsips.size;
    
    if (selectedCount) selectedCount.textContent = count;
    if (selectedCountModal) selectedCountModal.textContent = count;
    
    if (count > 0) {
        if (selectedCounter) selectedCounter.style.display = 'flex';
        prosesMultipleBtn.style.display = 'flex';
        updateArsipListInModal();
    } else {
        if (selectedCounter) selectedCounter.style.display = 'none';
        prosesMultipleBtn.style.display = 'none';
        selectedArsipList.innerHTML = '';
    }
    
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
                const subBagian = row.cells[4].textContent.trim();
                
                const item = document.createElement('div');
                item.className = 'mb-2 p-2 border rounded bg-white';
                item.innerHTML = `
                    <div>
                        <strong>${kode}</strong>
                        <div class="small text-muted">${judul}</div>
                        <div class="small">Sub Bagian: ${subBagian}</div>
                    </div>
                    <input type="hidden" name="arsip_ids[]" value="${id}">
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
    const clearSelectionBtn = document.getElementById('clearSelection');
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
        filterModalContainer.style.display = 'none';
        prosesMultipleModalContainer.style.display = 'none';
        importModalContainer.style.display = 'none';
        exportModalContainer.style.display = 'none';
        cekDuplikatModalContainer.style.display = 'none';
        modalOverlay.style.display = 'block';
        modalContainer.style.display = 'flex';
        modalContainer.classList.add('active');
        document.body.classList.add('modal-open');
    }
    
    // Fungsi untuk menutup modal
    function closeModal() {
        modalOverlay.style.display = 'none';
        filterModalContainer.style.display = 'none';
        prosesMultipleModalContainer.style.display = 'none';
        importModalContainer.style.display = 'none';
        exportModalContainer.style.display = 'none';
        cekDuplikatModalContainer.style.display = 'none';
        filterModalContainer.classList.remove('active');
        prosesMultipleModalContainer.classList.remove('active');
        document.body.classList.remove('modal-open');
    }
    
    // Event listeners untuk tombol buka modal
    if (openFilterBtn) {
        openFilterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(filterModalContainer);
        });
    }
    
    if (prosesMultipleBtn) {
        prosesMultipleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (selectedArsips.size === 0) {
                alert('Pilih setidaknya satu arsip terlebih dahulu.');
                return;
            }
            openModal(prosesMultipleModalContainer);
        });
    }
    
    // Event listeners untuk tombol tutup modal
    if (closeFilterBtn) {
        closeFilterBtn.addEventListener('click', closeModal);
    }
    
    if (closeProsesMultipleBtn) {
        closeProsesMultipleBtn.addEventListener('click', closeModal);
    }
    
    if (cancelProsesMultipleBtn) {
        cancelProsesMultipleBtn.addEventListener('click', closeModal);
    }
    
    // Tutup modal saat klik overlay
    if (modalOverlay) {
        modalOverlay.addEventListener('click', closeModal);
    }
    
    // Mencegah modal tertutup saat klik di dalam modal
    [filterModalContainer, prosesMultipleModalContainer].forEach(modal => {
        if (modal) {
            modal.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });
    
    // Reset filter
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
            window.location.href = "{{ route('arsip-masuk.index') }}";
        });
    }
    
    // Tampilkan/sembunyikan fields berdasarkan aksi
    if (multipleActionSelect) {
    multipleActionSelect.addEventListener('change', function() {
        setujuiFields.style.display = 'none';
        tolakFields.style.display = 'none';

        if (this.value === 'setujui') {
            setujuiFields.style.display = 'block';
        }

        if (this.value === 'tolak') {
            tolakFields.style.display = 'block';
        }
    });
}
    
    // =========================
    // FILTER RAK & BOX MULTIPLE
    // =========================
    
    // Filter rak berdasarkan lokasi
    function filterRakMultiple() {
        const selectedLokasi = lokasiTujuanMultiple.value;
        rakSelectMultiple.innerHTML = '<option value="">-- Pilih Rak --</option>';
        boxSelectMultiple.innerHTML = '<option value="">-- Pilih Rak Terlebih Dahulu --</option>';
        
        if (!selectedLokasi) {
            rakSelectMultiple.innerHTML = '<option value="">-- Pilih Lokasi Terlebih Dahulu --</option>';
            return;
        }
        
        const filteredRak = rakData.filter(function(rak) {
            return rak.lokasi_arsip === selectedLokasi;
        });
        
        if (filteredRak.length === 0) {
            rakSelectMultiple.innerHTML = '<option value="">-- Tidak ada rak di lokasi ini --</option>';
        } else {
            filteredRak.forEach(function(rak) {
                const option = document.createElement('option');
                option.value = rak.id;
                option.textContent = rak.nomor_rak;
                rakSelectMultiple.appendChild(option);
            });
        }
    }
    
    // Filter box berdasarkan rak
    function filterBoxMultiple() {
        const selectedRakId = rakSelectMultiple.value;
        boxSelectMultiple.innerHTML = '<option value="">-- Pilih Box --</option>';
        
        if (!selectedRakId) {
            boxSelectMultiple.innerHTML = '<option value="">-- Pilih Rak Terlebih Dahulu --</option>';
            return;
        }
        
        const filteredBox = boxData.filter(function(box) {
            return box.rak_id == selectedRakId;
        });
        
        if (filteredBox.length === 0) {
            boxSelectMultiple.innerHTML = '<option value="">-- Tidak ada box di rak ini --</option>';
        } else {
            filteredBox.forEach(function(box) {
                const option = document.createElement('option');
                option.value = box.id;
                option.textContent = box.nomor_box;
                boxSelectMultiple.appendChild(option);
            });
        }
    }
    
    // Event listeners untuk filter
    if (lokasiTujuanMultiple) {
        lokasiTujuanMultiple.addEventListener('change', filterRakMultiple);
    }
    if (rakSelectMultiple) {
        rakSelectMultiple.addEventListener('change', filterBoxMultiple);
    }
    
    // =========================
    // INLINE EDIT FUNCTIONALITY
    // =========================
    
    // Click on editable field to open inline edit
    document.querySelectorAll('.editable-click').forEach(field => {
        field.addEventListener('click', function(e) {
            // Jika sudah dalam mode edit, jangan buat lagi
            if (this.querySelector('input') || this.querySelector('select')) {
                return;
            }
            
            const fieldName = this.dataset.field;
            const arsipId = this.dataset.id;
            const type = this.dataset.type || 'text';
            const currentValue = this.textContent.trim();
            
            // Hanya izinkan edit untuk field tertentu
            const allowedFields = ['kode_klasifikasi', 'aktif_tahun', 'inaktif_tahun', 'keterangan_jra'];
            if (!allowedFields.includes(fieldName)) {
                return;
            }
            
            startInlineEdit(this, fieldName, arsipId, type, currentValue);
        });
    });
    
    // Function to start inline editing
    function startInlineEdit(element, fieldName, arsipId, type, currentValue) {
        const parent = element.parentElement;
        const originalText = element.textContent;
        
        // Mark as editing
        element.classList.add('editing');
        
        let inputElement;
        
        if (type === 'select') {
            // Create select dropdown
            inputElement = document.createElement('select');
            inputElement.className = 'inline-edit-select';
            
            let options = [];
            if (fieldName === 'kode_klasifikasi') {
                options = kodeKlasifikasiOptions.map(item => ({
                    value: item.id,
                    label: item.kode + ' - ' + item.uraian
                }));
            } else if (fieldName === 'keterangan_jra') {
                options = keteranganJraOptions.map(item => ({
                    value: item,
                    label: item
                }));
            }
            
            // Add empty option
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = '-- Pilih --';
            inputElement.appendChild(emptyOption);
            
            // Add options
            options.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.label;
                if (opt.label === originalText || opt.value == originalText) {
                    option.selected = true;
                }
                inputElement.appendChild(option);
            });
            
        } else {
            // Create input for number/text
            inputElement = document.createElement('input');
            inputElement.type = type === 'number' ? 'number' : 'text';
            inputElement.className = 'inline-edit-input';
            inputElement.value = currentValue === '-' ? '' : currentValue;
            if (type === 'number') {
                inputElement.min = 0;
                inputElement.step = 1;
            }
        }
        
        // Clear element and add input
        element.innerHTML = '';
        element.appendChild(inputElement);
        
        // Focus and select
        if (type !== 'select') {
            inputElement.focus();
            inputElement.select();
        }
        
        // Handle blur (save on exit)
        inputElement.addEventListener('blur', function(e) {
            // Jangan save jika klik pada option (untuk select)
            if (type === 'select') {
                setTimeout(() => {
                    if (!document.activeElement || document.activeElement.tagName !== 'SELECT') {
                        saveInlineEdit(element, fieldName, arsipId, originalText);
                    }
                }, 200);
                return;
            }
            saveInlineEdit(element, fieldName, arsipId, originalText);
        });
        
        // Handle Enter key
        inputElement.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.blur();
            }
            if (e.key === 'Escape') {
                e.preventDefault();
                // Cancel edit
                element.textContent = originalText;
                element.classList.remove('editing');
            }
        });
        
        // For select, save on change
        if (type === 'select') {
            inputElement.addEventListener('change', function() {
                saveInlineEdit(element, fieldName, arsipId, originalText);
            });
        }
    }
    
    // Function to save inline edit
   // Function to save inline edit
function saveInlineEdit(element, fieldName, arsipId, originalText) {
    const input = element.querySelector('input, select');
    if (!input) {
        element.classList.remove('editing');
        return;
    }
    
    let newValue = input.value;
    const type = element.dataset.type || 'text';
    
    // Jika select dan nilai kosong, gunakan original
    if (type === 'select' && !newValue) {
        newValue = originalText;
    }
    
    // Jika nilai tidak berubah
    if (newValue === originalText || (originalText === '-' && !newValue)) {
        element.textContent = originalText || '-';
        element.classList.remove('editing');
        return;
    }
    
    // Untuk number, validasi
    if (type === 'number') {
        const numValue = parseInt(newValue);
        if (isNaN(numValue) || numValue < 0) {
            element.textContent = originalText || '-';
            element.classList.remove('editing');
            showToast('error', 'Nilai harus berupa angka positif');
            return;
        }
        newValue = numValue;
    }
    
    // Show loading
    element.innerHTML = '<span class="text-muted"><i class="bi bi-hourglass-split"></i> Menyimpan...</span>';
    
    // Send AJAX request
    const url = element.dataset.url || `/arsip-masuk/${arsipId}/update-field`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            field: fieldName,
            value: newValue
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update display dengan data dari server
            const result = data.data;
            
            if (fieldName === 'aktif_tahun') {
                element.textContent = result.aktif_tahun || '-';
                updateRelatedField(arsipId, 'aktif_sampai', result.aktif_sampai);
                if (result.status_arsip) {
                    updateRelatedField(arsipId, 'status', result.status_arsip);
                }
            } else if (fieldName === 'inaktif_tahun') {
                element.textContent = result.inaktif_tahun || '-';
                updateRelatedField(arsipId, 'inaktif_sampai', result.inaktif_sampai);
                if (result.status_arsip) {
                    updateRelatedField(arsipId, 'status', result.status_arsip);
                }
            } else if (fieldName === 'keterangan_jra') {
                element.textContent = result.keterangan_jra || '-';
                // TAMBAHKAN INI: keterangan_jra juga mempengaruhi retensi
                updateRelatedField(arsipId, 'aktif_sampai', result.aktif_sampai);
                updateRelatedField(arsipId, 'inaktif_sampai', result.inaktif_sampai);
            } else if (fieldName === 'kode_klasifikasi') {
                element.textContent = result.kode_klasifikasi || '-';
            }

            // Status selalu diupdate kalau ada perubahan (satu kali saja, tidak perlu diulang di setiap cabang di atas)
            if (result.status_arsip) {
                updateRelatedField(arsipId, 'status', result.status_arsip);
            }
            
            element.classList.remove('editing');
            showToast('success', data.message || 'Data berhasil diperbarui');
        } else {
            element.textContent = originalText || '-';
            element.classList.remove('editing');
            showToast('error', data.message || 'Gagal memperbarui data');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        element.textContent = originalText || '-';
        element.classList.remove('editing');
        showToast('error', 'Terjadi kesalahan. Silakan coba lagi.');
    });
}

// Helper function untuk update field terkait
function updateRelatedField(arsipId, fieldName, value) {
    const row = document.querySelector(`tr[data-id="${arsipId}"]`);
    if (!row) return;
    
    // Cari cell berdasarkan indeks
    const cellIndexMap = {
        'aktif_sampai': 12, // indeks kolom Aktif Sampai
        'inaktif_sampai': 13, // indeks kolom Inaktif Sampai
        'status': 14 // indeks kolom Status
    };
    
    const cellIndex = cellIndexMap[fieldName];
    if (cellIndex === undefined) return;
    
    const cell = row.cells[cellIndex];
    if (!cell) return;
    
    const fieldElement = cell.querySelector('.editable-field');
    if (fieldElement) {
        if (fieldName === 'status') {
            // Untuk status, tampilkan badge
            const statusColors = {
                'AKTIF': 'success',
                'INAKTIF': 'warning',
                'PERMANEN': 'info',
                'MUSNAH': 'danger',
                'HABIS_RETENSI': 'secondary'
            };
            const color = statusColors[value] || 'secondary';
            fieldElement.innerHTML = `<span class="badge bg-${color}">${value}</span>`;
        } else {
            fieldElement.textContent = value || '-';
        }
    }
}
    
    // Toast notification function
    function showToast(type, message) {
        // Remove existing toast
        const existingToast = document.querySelector('.toast-container');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Create toast container
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '99999';
        
        // Create toast
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 show`;
        toast.role = 'alert';
        toast.ariaLive = 'assertive';
        toast.ariaAtomic = 'true';
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        container.appendChild(toast);
        document.body.appendChild(container);
        
        // Auto dismiss after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => container.remove(), 300);
        }, 3000);
    }
    
    // Validasi form proses multiple
    if (prosesMultipleForm) {
    prosesMultipleForm.addEventListener('submit', function(e) {
        if (selectedArsips.size === 0) {
            e.preventDefault();
            alert('Pilih setidaknya satu arsip untuk diproses.');
            return;
        }

        const action = document.getElementById('multipleActionSelect').value;
        if (!action) {
            e.preventDefault();
            alert('Pilih aksi yang akan dilakukan.');
            return;
        }

        // Validasi untuk SETUJUI
        if (action === 'setujui') {
            // const statusArsip = document.getElementById('status_arsip_multiple').value;
            const lokasi = lokasiTujuanMultiple.value;
            const rakId = rakSelectMultiple.value;
            const boxId = boxSelectMultiple.value;

            // if (!statusArsip) {
            //     e.preventDefault();
            //     alert('Pilih status arsip setelah dipindah.');
            //     return;
            // }

            if (!lokasi) {
                e.preventDefault();
                alert('Pilih lokasi tujuan terlebih dahulu.');
                return;
            }

            if (!rakId) {
                e.preventDefault();
                alert('Pilih rak tujuan.');
                return;
            }

            if (!boxId) {
                e.preventDefault();
                alert('Pilih box tujuan.');
                return;
            }

            const rakName = rakSelectMultiple.options[rakSelectMultiple.selectedIndex]?.text || '-';
            const boxName = boxSelectMultiple.options[boxSelectMultiple.selectedIndex]?.text || '-';
            const lokasiLabel = lokasiTujuanMultiple.options[lokasiTujuanMultiple.selectedIndex]?.text || '-';

            if (!confirm('Anda akan MENYETUJUI dan memindahkan ' + selectedArsips.size + ' arsip ke:\n' +
                        'Lokasi: ' + lokasiLabel + '\n' +
                        'Rak: ' + rakName + '\n' +
                        'Box: ' + boxName + '\n\n' +
                        'Lanjutkan?')) {
                e.preventDefault();
                return;
            }
        }

        // Validasi untuk TOLAK
        if (action === 'tolak') {
            const alasan = document.getElementById('alasan_multiple').value.trim();

            if (!alasan) {
                e.preventDefault();
                alert('Isi alasan penolakan.');
                document.getElementById('alasan_multiple').focus();
                return;
            }

            if (!confirm('Anda akan MENOLAK ' + selectedArsips.size + ' arsip.\n\n' +
                        'Arsip akan dikembalikan ke Sub Bagian untuk diperbaiki.\n\n' +
                        'Lanjutkan?')) {
                e.preventDefault();
                return;
            }
        }

        // Tampilkan loading
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Memproses...
        `;
        submitBtn.disabled = true;
    });
}
    
    // Tutup modal dengan tombol ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalOverlay.style.display === 'block') {
            closeModal();
        }
    });

    // =========================
    // IMPORT MODAL
    // =========================
    const openImportBtn = document.getElementById('openImportModal');
    const closeImportBtn = document.getElementById('closeImportModal');
    const cancelImportBtn = document.getElementById('cancelImport');
    const importForm = document.getElementById('importForm');
    const btnSubmitImport = document.getElementById('btnSubmitImport');

    if (openImportBtn) {
        openImportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(importModalContainer);
        });
    }
    if (closeImportBtn) closeImportBtn.addEventListener('click', closeModal);
    if (cancelImportBtn) cancelImportBtn.addEventListener('click', closeModal);

    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('file_import');
            if (!fileInput.files.length) {
                e.preventDefault();
                alert('Silakan pilih file Excel terlebih dahulu.');
                return;
            }
            btnSubmitImport.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
            btnSubmitImport.disabled = true;
        });
    }

    if (importModalContainer) {
        importModalContainer.addEventListener('click', function(e) { e.stopPropagation(); });
    }

    // =========================
// EXPORT MODAL
// =========================
const openExportBtn = document.getElementById('openExportModal');
const closeExportBtn = document.getElementById('closeExportModal');
const cancelExportBtn = document.getElementById('cancelExport');

if (openExportBtn) {
    openExportBtn.addEventListener('click', function(e) {
        e.preventDefault();
        openModal(exportModalContainer);
    });
}
if (closeExportBtn) closeExportBtn.addEventListener('click', closeModal);
if (cancelExportBtn) cancelExportBtn.addEventListener('click', closeModal);

if (exportModalContainer) {
    exportModalContainer.addEventListener('click', function(e) { e.stopPropagation(); });
}

    // =========================
    // CEK DUPLIKAT MODAL
    // =========================
    const openCekDuplikatBtn = document.getElementById('openCekDuplikatModal');
    const closeCekDuplikatBtn = document.getElementById('closeCekDuplikatModal');
    const closeCekDuplikatFooterBtn = document.getElementById('closeCekDuplikatFooter');
    const cekDuplikatBody = document.getElementById('cekDuplikatBody');

    if (openCekDuplikatBtn) {
        openCekDuplikatBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(cekDuplikatModalContainer);
            loadCekDuplikat();
        });
    }
    if (closeCekDuplikatBtn) closeCekDuplikatBtn.addEventListener('click', closeModal);
    if (closeCekDuplikatFooterBtn) closeCekDuplikatFooterBtn.addEventListener('click', closeModal);

    if (cekDuplikatModalContainer) {
        cekDuplikatModalContainer.addEventListener('click', function(e) { e.stopPropagation(); });
    }

    function loadCekDuplikat() {
        cekDuplikatBody.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-danger" role="status"></div>
                <p class="mt-2 text-muted">Memeriksa data duplikat...</p>
            </div>`;

        fetch("{{ route('arsip-masuk.cek-duplikat') }}", {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            if (!res.success || res.total_kelompok_duplikat === 0) {
                cekDuplikatBody.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle-fill text-success fs-1"></i>
                        <p class="mt-2 text-muted">Tidak ditemukan data duplikat.</p>
                    </div>`;
                return;
            }

            let html = `<p class="text-muted">Ditemukan <strong>${res.total_kelompok_duplikat}</strong> kelompok arsip yang kemungkinan duplikat:</p>`;

            res.data.forEach(group => {
                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold mb-1">${group.judul}</h6>
                            <p class="small text-muted mb-2">
                                Kode: ${group.kode_klasifikasi} | Sub Bagian: ${group.sub_bagian} | Tahun: ${group.tahun_arsip}
                                | <span class="badge bg-danger">${group.jumlah} data serupa</span>
                            </p>
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr><th>ID</th><th>Status</th><th>Asal Data</th><th>Dibuat</th><th></th></tr>
                                </thead>
                                <tbody>
                                    ${group.items.map(item => `
                                        <tr>
                                            <td>${item.id}</td>
                                            <td>${item.status ?? '-'}</td>
                                            <td>${item.asal_data}</td>
                                            <td>${item.dibuat}</td>
                                            <td><a href="${item.url}" class="btn btn-sm btn-info" target="_blank"><i class="bi bi-eye"></i></a></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>`;
            });

            cekDuplikatBody.innerHTML = html;
        })
        .catch(() => {
            cekDuplikatBody.innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                    <p class="mt-2">Gagal memuat data duplikat. Coba lagi.</p>
                </div>`;
        });
    }
    
    // Inisialisasi selection UI
    updateSelectionUI();
});
</script>
@endsection