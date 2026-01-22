@extends('layouts.app')

@section('page-title', 'Kelola Arsip')
@section('page-subtitle', 'Manajemen Data Arsip Digital')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Arsip</h5>
            <div>
                <a href="{{ route('arsip.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Arsip
                </a>
                <button class="btn btn-outline-secondary" id="filterButton">
                    <i class="bi bi-filter"></i> Filter
                </button>
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
                        <th style="min-width: 100px;" class="sortable-header">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'no_sampul', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                <span>Sampul</span>
                                @if(request('sort') == 'no_sampul')
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
                    <tr>
                        <td>{{ $loop->iteration + ($arsips->currentPage() - 1) * $arsips->perPage() }}</td>
                        <td><strong>{{ $arsip->kodeKlasifikasi->kode ?? 'N/A' }}</strong></td>
                        <td>{{ Str::limit($arsip->uraian_arsip, 200) }}</td>
                        <td>{{ $arsip->tahun_arsip }}</td>
                        <td>{{ $arsip->nomor_rak }}</td>
                        <td>{{ $arsip->nomor_box }}</td>
                        <td>{{ $arsip->no_sampul ?? '-' }}</td>
                        <td>
                            @if($arsip->aktif_sampai)
                                {{ \Carbon\Carbon::parse($arsip->aktif_sampai)->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($arsip->inaktif_sampai)
                                {{ \Carbon\Carbon::parse($arsip->inaktif_sampai)->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'AKTIF' => 'success',
                                    'INAKTIF' => 'warning',
                                    'UMSUL_MUSNAH' => 'danger',
                                    'PERMANEN' => 'info'
                                ];
                                $color = $statusColors[$arsip->status_arsip] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">
                                @if($arsip->status_arsip == 'UMSUL_MUSNAH')
                                    Usul Musnah
                                @else
                                    {{ $arsip->status_arsip }}
                                @endif
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" style="gap: 6px;">
                                <a href="{{ route('arsip.show', $arsip->id) }}" class="btn btn-info" title="Detail" style="padding: 0.25rem 0.5rem;">
                                    <i class="bi bi-eye"></i>
                                </a> 
                                <a href="{{ route('arsip.edit', $arsip->id) }}" class="btn btn-warning" title="Edit" style="padding: 0.25rem 0.5rem;">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('arsip.destroy', $arsip->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus arsip ini?')">
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
                        <td colspan="11" class="text-center py-4">
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
            {{ $arsips->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div id="filterModal" class="custom-modal">
    <div class="custom-modal-overlay" onclick="closeModal()"></div>
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h5 class="mb-0">Filter Arsip</h5>
            <button type="button" onclick="closeModal()" class="custom-modal-close">&times;</button>
        </div>
        <form method="GET" action="{{ route('arsip.index') }}">
            <div class="custom-modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status Arsip</label>
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
                        <label class="form-label">Tahun Arsip</label>
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
                        <label class="form-label">Sub Bagian</label>
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
                        <label for="keterangan" class="form-label">Kondisi Fisik</label>
                        <select class="form-select" id="keterangan" name="keterangan">
                            <option value="">Semua Kondisi</option>
                            <option value="BAIK" {{ request('keterangan') == 'BAIK' ? 'selected' : '' }}>Baik</option>
                            <option value="RUSAK" {{ request('keterangan') == 'RUSAK' ? 'selected' : '' }}>Rusak</option>
                            <option value="HILANG" {{ request('keterangan') == 'HILANG' ? 'selected' : '' }}>Hilang</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Klasifikasi</label>
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
                        <label class="form-label">Rak</label>
                        <input type="text" name="nomor_rak" class="form-control" value="{{ request('nomor_rak') }}" placeholder="Nomor Rak">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Box</label>
                        <input type="text" name="nomor_box" class="form-control" value="{{ request('nomor_box') }}" placeholder="Nomor Box">
                    </div>
                </div>
            </div>
            <div class="custom-modal-footer">
                <a href="{{ route('arsip.index') }}" class="btn btn-secondary">Reset</a>
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* ===== MODAL STYLE DENGAN Z-INDEX TINGGI ===== */
    .custom-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 999999; /* Sangat tinggi untuk mengalahkan semua elemen */
    }
    
    .custom-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1;
    }
    
    .custom-modal-content {
        position: relative;
        background: white;
        border-radius: 8px;
        width: 90%;
        max-width: 600px;
        max-height: 85vh;
        overflow-y: auto;
        margin: 50px auto;
        z-index: 2;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        animation: modalFadeIn 0.3s ease;
    }
    
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .custom-modal-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #dee2e6;
        background: #f8f9fa;
        border-radius: 8px 8px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 3;
    }
    
    .custom-modal-body {
        padding: 1.5rem;
    }
    
    .custom-modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #dee2e6;
        background: #f8f9fa;
        border-radius: 0 0 8px 8px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        position: sticky;
        bottom: 0;
        z-index: 3;
    }
    
    .custom-modal-close {
        background: none;
        border: none;
        font-size: 1.8rem;
        line-height: 1;
        cursor: pointer;
        color: #6c757d;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: all 0.2s;
    }
    
    .custom-modal-close:hover {
        background: rgba(0, 0, 0, 0.1);
        color: #000;
    }
    
    /* Pastikan modal di atas semua elemen */
    body.modal-open {
        overflow: hidden;
    }
    
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
    
    /* Simbol sorting selalu terlihat */
    .sortable-header i {
        font-size: 14px;
        transition: all 0.2s;
        visibility: visible !important;
        opacity: 1 !important;
        display: inline-block !important;
    }
    
    /* Simbol sorting default (tidak aktif) - WARNA ABU */
    .sortable-header .bi-caret-up-down {
        color: #6c757d !important;
        opacity: 0.8;
    }
    
    /* Simbol sorting aktif - WARNA HITAM */
    .sortable-header .bi-caret-up-fill.text-dark,
    .sortable-header .bi-caret-down-fill.text-dark {
        color: #212529 !important;
        opacity: 1;
        font-weight: bold;
    }
    
    /* Hover effect untuk simbol sorting */
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
    
    /* Pastikan elemen lain tidak memiliki z-index lebih tinggi */
    .navbar, .sidebar, .footer {
        z-index: auto !important;
    }
</style>

<script>
    // Fungsi untuk menampilkan modal
    document.getElementById('filterButton').addEventListener('click', function() {
        const modal = document.getElementById('filterModal');
        modal.style.display = 'block';
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
    });
    
    function closeModal() {
        const modal = document.getElementById('filterModal');
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
        document.body.style.overflow = 'auto';
    }
    
    // Close modal dengan ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    
    // Saat halaman dimuat, tambahkan event listener untuk overlay
    document.addEventListener('DOMContentLoaded', function() {
        // Cegah event bubbling
        const modalContent = document.querySelector('.custom-modal-content');
        if (modalContent) {
            modalContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        // Pastikan z-index modal adalah yang tertinggi
        const modal = document.getElementById('filterModal');
        if (modal) {
            // Force set z-index tinggi
            modal.style.zIndex = '999999';
        }
        
        // Tambahkan hover effect untuk sortable headers
        const sortableHeaders = document.querySelectorAll('.sortable-header');
        sortableHeaders.forEach(header => {
            header.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#e9ecef';
            });
            
            header.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '#f8f9fa';
            });
        });
    });
</script>
@endsection