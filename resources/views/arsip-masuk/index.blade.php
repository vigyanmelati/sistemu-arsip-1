@extends('layouts.app')

@section('page-title', 'Daftar Arsip Masuk')
@section('page-subtitle', 'Verifikasi Arsip yang Diajukan Subbagian')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Arsip Masuk</h5>
            <div class="action-buttons d-flex gap-2">
                <button class="btn btn-cyan d-flex align-items-center gap-2" id="openFilterModal">
                    <i class="bi bi-funnel-fill"></i>
                    <span>Filter</span>
                </button>
                <button class="btn btn-warning d-flex align-items-center gap-2 shadow-sm" id="prosesMultipleBtn" style="display: none;">
                    <i class="bi bi-gear-fill"></i>
                    <span>Proses Multiple</span>
                </button>
                <!-- <a href="{{ route('arsip-masuk.dashboard') }}" class="btn btn-info d-flex align-items-center gap-2">
                    <i class="bi bi-graph-up"></i>
                    <span>Dashboard</span>
                </a> -->
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
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-warning bg-opacity-10 border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-warning mb-0">Diajukan</h6>
                                <h3 class="mb-0">{{ $arsips->total() }}</h3>
                            </div>
                            <i class="bi bi-clock-history text-warning fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="col-md-3">
                <div class="card bg-success bg-opacity-10 border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-success mb-0">Diterima</h6>
                                <h3 class="mb-0">{{ App\Models\Arsip::where('status_pindah', 'DITERIMA')->count() }}</h3>
                            </div>
                            <i class="bi bi-check-circle text-success fs-1"></i>
                        </div>
                    </div>
                </div>
            </div> -->
            <div class="col-md-3">
                <div class="card bg-danger bg-opacity-10 border-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-danger mb-0">Ditolak</h6>
                                <h3 class="mb-0">{{ App\Models\Arsip::where('status_pindah', 'DITOLAK')->count() }}</h3>
                            </div>
                            <i class="bi bi-x-circle text-danger fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info bg-opacity-10 border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-info mb-0">Dipindahkan</h6>
                                <h3 class="mb-0">{{ App\Models\Arsip::where('status_pindah', 'DIPINDAHKAN')->count() }}</h3>
                            </div>
                            <i class="bi bi-archive text-info fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
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
        
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        
        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>#</th>
                        <th>Kode Klasifikasi</th>
                        <th>Judul Arsip</th>
                        <th>Sub Bagian</th>
                        <th>Tahun</th>
                        <th>Jumlah</th>
                        <th>Tanggal Diajukan</th>
                        <th>File Berita Acara</th>
                        <th>Status Pemindahan</th>
                        <th>Aksi</th>
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
                        <td>{{ Str::limit($arsip->uraian_arsip, 150) }}</td>
                        <td>{{ $arsip->subBagian->nama_sub_bagian ?? 'N/A' }}</td>
                        <td>{{ $arsip->tahun_arsip }}</td>
                        <td>{{ $arsip->jumlah_berkas }} {{ $arsip->satuan_arsip }}</td>
                        <td>{{ $arsip->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($arsip->file_berita_acara)
                                <a href="{{ route('admin.arsip-masuk.download-berita-acara', $arsip->id) }}" 
                                   class="btn btn-sm btn-success" 
                                   title="Download Berita Acara">
                                    <i class="bi bi-download"></i> Download
                                </a>
                            @else
                                <span class="badge bg-warning">Tidak ada</span>
                            @endif
                        </td>
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
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('arsip-masuk.show', $arsip->id) }}" 
                                   class="btn btn-info" 
                                   title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-4">
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
            {{ $arsips->withQueryString()->links() }}
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
                    </div>
                </div>
                <div class="modal-footer bg-light">
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
                        <label class="form-label fw-semibold">Aksi</label>
                        <select name="action" class="form-select" id="multipleActionSelect" required>
                            <option value="">Pilih Aksi</option>
                            <option value="terima">Terima</option>
                            <option value="tolak">Tolak</option>
                            <option value="pindahkan">Pindahkan ke Master</option>
                        </select>
                    </div>

                    <!-- Fields for Terima -->
                    <div id="terimaFields" style="display: none;">
                        <!-- <div class="mb-3">
                            <label class="form-label fw-semibold">Nomor Berita Acara Penerimaan <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_berita_acara_penerimaan" class="form-control" placeholder="Contoh: 001/BA-PA/III/2024">
                        </div> -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lokasi Baru di Unit Kearsipan <span class="text-danger">*</span></label>
                            <input type="text" name="lokasi_baru" class="form-control" placeholder="Contoh: Rak A-01, Box B-01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan (Opsional)</label>
                            <textarea name="catatan" class="form-control" rows="2" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                        </div>
                    </div>

                    <!-- Fields for Tolak -->
                    <div id="tolakFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="catatan" class="form-control" rows="3" placeholder="Sebutkan alasan penolakan..." required></textarea>
                        </div>
                    </div>

                    <!-- Fields for Pindahkan -->
                    <div id="pindahkanFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status Arsip setelah Dipindahkan <span class="text-danger">*</span></label>
                            <select name="status_arsip_setelah_pindah" class="form-select" required>
                                <option value="AKTIF">Aktif</option>
                                <option value="INAKTIF">Inaktif</option>
                                <option value="PERMANEN">Permanen</option>
                                <option value="MUSNAH">Musnah</option>
                            </select>
                        </div>
                        <!-- <div class="mb-3">
                            <label class="form-label fw-semibold">Nomor Berita Acara Penerimaan <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_berita_acara_penerimaan" class="form-control" placeholder="Contoh: 001/BA-PA/III/2024" required>
                        </div> -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lokasi Baru di Unit Kearsipan <span class="text-danger">*</span></label>
                            <input type="text" name="lokasi_baru" class="form-control" placeholder="Contoh: Rak A-01, Box B-01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan (Opsional)</label>
                            <textarea name="catatan" class="form-control" rows="2" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
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
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-gear-fill me-1"></i> Proses Sekarang
                    </button>
                </div>
            </form>
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const modalOverlay = document.getElementById('modalOverlay');
    const filterModalContainer = document.getElementById('filterModalContainer');
    const prosesMultipleModalContainer = document.getElementById('prosesMultipleModalContainer');
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
    const terimaFields = document.getElementById('terimaFields');
    const tolakFields = document.getElementById('tolakFields');
    const pindahkanFields = document.getElementById('pindahkanFields');
    
    const prosesMultipleForm = document.getElementById('prosesMultipleForm');
    
    // Selection Management
    let selectedArsips = new Set();
    
    // Fungsi untuk update selection UI
    function updateSelectionUI() {
        const count = selectedArsips.size;
        selectedCount.textContent = count;
        selectedCountModal.textContent = count;
        
        if (count > 0) {
            selectedCounter.style.display = 'flex!important';
            prosesMultipleBtn.style.display = 'flex';
            updateArsipListInModal();
        } else {
            selectedCounter.style.display = 'none!important';
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
            // Sembunyikan semua fields terlebih dahulu
            terimaFields.style.display = 'none';
            tolakFields.style.display = 'none';
            pindahkanFields.style.display = 'none';
            
            // Tampilkan fields yang sesuai
            if (this.value === 'terima') {
                terimaFields.style.display = 'block';
            } else if (this.value === 'tolak') {
                tolakFields.style.display = 'block';
            } else if (this.value === 'pindahkan') {
                pindahkanFields.style.display = 'block';
            }
        });
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
    
    // Inisialisasi selection UI
    updateSelectionUI();
});
</script>
@endsection