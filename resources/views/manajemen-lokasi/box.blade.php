@extends('layouts.app')

@section('page-title', 'Manajemen Lokasi - Box')
@section('page-subtitle', 'Ruangan: ' . $ruanganLabel . ' | Rak: ' . $rak)

@section('content')
{{-- Alert Sukses --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Alert Error --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid px-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('manajemen-lokasi.index') }}" class="text-decoration-none">Ruangan</a></li>
            <li class="breadcrumb-item"><a href="{{ route('manajemen-lokasi.rak', $ruangan) }}" class="text-decoration-none">{{ $ruanganLabel }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Rak {{ $rak }}</li>
        </ol>
    </nav>

    {{-- Tombol Kembali & Tambah Box --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <a href="{{ route('manajemen-lokasi.rak', $ruangan) }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Rak
        </a>
        <button type="button" class="btn btn-success rounded-pill" data-bs-toggle="modal" data-bs-target="#modalTambahBox">
            <i class="bi bi-plus-circle me-1"></i> Tambah Box
        </button>
    </div>

    {{-- Header & Pencarian --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-semibold mb-0">
                <i class="bi bi-box-seam-fill me-2 text-success"></i> Daftar Box
            </h3>
            <p class="text-muted mt-1 mb-0">
                Ruangan: <strong>{{ $ruanganLabel }}</strong> | Rak: <strong>{{ $rak }}</strong> — Pilih box untuk melihat arsip.
            </p>
        </div>
        <div class="search-box" style="min-width: 250px;">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="searchBox" class="form-control border-start-0 ps-0" placeholder="Cari box...">
                <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Reset pencarian">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Grid Kartu Box --}}
    <div class="row g-4" id="boxGrid">
        @php
            $boxIcons = [
                'bi bi-box-seam-fill',
                'bi bi-box-fill',
                'bi bi-box2-fill',
                'bi bi-box2-heart-fill',
                'bi bi-archive-fill',
                'bi bi-inbox-fill',
                'bi bi-cube-fill',
                'bi bi-grid-3x3-gap-fill'
            ];
            $gradientColors = [
                'linear-gradient(90deg, #198754, #20c997)',
                'linear-gradient(90deg, #0d6efd, #0dcaf0)',
                'linear-gradient(90deg, #dc3545, #f87171)',
                'linear-gradient(90deg, #fd7e14, #ffc107)',
                'linear-gradient(90deg, #6f42c1, #a78bfa)',
                'linear-gradient(90deg, #d63384, #f472b6)',
                'linear-gradient(90deg, #20c997, #198754)',
                'linear-gradient(90deg, #0dcaf0, #0d6efd)'
            ];
            $iconColors = [
                '#198754', '#0d6efd', '#dc3545', '#fd7e14', 
                '#6f42c1', '#d63384', '#20c997', '#0dcaf0'
            ];
            $bgColors = [
                'rgba(25, 135, 84, 0.1)',
                'rgba(13, 110, 253, 0.1)',
                'rgba(220, 53, 69, 0.1)',
                'rgba(253, 126, 20, 0.1)',
                'rgba(111, 66, 193, 0.1)',
                'rgba(214, 51, 132, 0.1)',
                'rgba(32, 201, 151, 0.1)',
                'rgba(13, 202, 240, 0.1)'
            ];
        @endphp

        @forelse($boxes as $index => $box)
            @php
                $boxName = $box->nomor_box;
                $iconClass = $boxIcons[$index % count($boxIcons)];
                $iconColor = $iconColors[$index % count($iconColors)];
                $bgColor = $bgColors[$index % count($bgColors)];
                $gradientBorder = $gradientColors[$index % count($gradientColors)];
            @endphp

            <div class="col-sm-6 col-lg-4 col-xl-3 box-card" data-box="{{ strtolower($boxName) }}">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4 overflow-hidden position-relative">
                    <!-- Border atas gradien -->
                    <div class="card-top-border" style="height: 6px; background: {{ $gradientBorder }}; width: 100%;"></div>

                    <!-- Tombol aksi di pojok kanan atas (ikon) -->
                    <div class="position-absolute top-0 end-0 p-2" style="z-index: 10;">
                        <button type="button" class="btn btn-sm btn-link text-primary p-1" 
                                data-bs-toggle="modal" data-bs-target="#modalEditBox"
                                data-id="{{ $box->id }}"
                                data-nomor="{{ $box->nomor_box }}"
                                data-keterangan="{{ $box->keterangan }}"
                                title="Edit Box">
                            <i class="bi bi-pencil-square fs-6"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-link text-danger p-1" 
                                data-bs-toggle="modal" data-bs-target="#modalHapusBox"
                                data-id="{{ $box->id }}"
                                data-nomor="{{ $box->nomor_box }}"
                                title="Hapus Box">
                            <i class="bi bi-trash3 fs-6"></i>
                        </button>
                    </div>

                    <a href="{{ route('manajemen-lokasi.arsip', [$ruangan, $rak, $boxName]) }}" class="text-decoration-none d-block h-100">
                        <div class="card-body text-center p-4">
                            <!-- Icon -->
                            <div class="icon-wrapper rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                 style="width: 85px; height: 85px; background: {{ $bgColor }}; transition: all 0.3s;">
                                <i class="{{ $iconClass }} fs-1" style="color: {{ $iconColor }}; font-size: 3rem !important;"></i>
                            </div>
                            
                            <h5 class="card-title fw-semibold mb-2" style="color: {{ $iconColor }};">{{ $boxName }}</h5>
                            
                            {{-- Keterangan --}}
                            @if(!empty($box->keterangan))
                                <p class="text-muted small mb-3" style="font-size: 0.85rem;">
                                    <i class="bi bi-info-circle me-1"></i> {{ $box->keterangan }}
                                </p>
                            @endif

                            {{-- Badge --}}
                         <div class="mb-3 d-flex justify-content-center gap-2 flex-wrap">
                            <span class="badge px-3 py-2 rounded-pill" style="background: {{ $bgColor }}; color: {{ $iconColor }}; font-weight: 500;">
                                <i class="bi bi-files me-1"></i> Kelola arsip
                            </span>
                            <span class="badge px-3 py-2 rounded-pill {{ $box->jumlah_arsip > 0 ? 'bg-success' : 'bg-secondary' }}">
                                <i class="bi bi-archive me-1"></i> {{ $box->jumlah_arsip }} Arsip
                            </span>
                        </div>
                            
                            {{-- Tombol Lihat Arsip --}}
                            <div class="d-grid gap-2">
                                <span class="btn btn-sm view-archive-btn" 
                                      style="border: 2px solid {{ $iconColor }}; color: {{ $iconColor }}; background: white; border-radius: 50px; font-weight: 500; transition: all 0.3s;">
                                    <i class="bi bi-eye me-1"></i> Lihat Arsip
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center py-5 border-0 shadow-sm rounded-4">
                    <i class="bi bi-box-seam fs-1 d-block mb-3"></i>
                    <h5 class="fw-semibold">Belum ada box di rak ini</h5>
                    <p class="mb-0">Klik tombol <strong>"Tambah Box"</strong> di atas untuk membuat box baru.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pesan jika pencarian tidak menemukan hasil --}}
    <div id="noResultMessage" class="text-center py-5 d-none">
        <i class="bi bi-search-slash fs-1 text-muted"></i>
        <h5 class="mt-3">Box tidak ditemukan</h5>
        <p class="text-muted">Coba gunakan kata kunci lain.</p>
    </div>
</div>

{{-- ===================== MODAL TAMBAH BOX ===================== --}}
<div class="modal fade" id="modalTambahBox" tabindex="-1" aria-labelledby="modalTambahBoxLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('manajemen-lokasi.store-box') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahBoxLabel">
                        <i class="bi bi-box-seam-fill me-2 text-success"></i>Tambah Box Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="rak_id" value="{{ $rakId ?? '' }}">
                    <div class="mb-3">
                        <label for="nomor_box" class="form-label" style="font-weight:bold">Nomor Box <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nomor_box" name="nomor_box" placeholder="Contoh: Box-01-Umlog" required>
                        <small class="text-muted d-block mt-1">Gunakan format penamaan yang konsisten, misalnya <strong>Box-[Nomor]-[Nama Ruangan]</strong>.</small>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan_box" class="form-label" style="font-weight:bold">Keterangan</label>
                        <input type="text" class="form-control" id="keterangan_box" name="keterangan" placeholder="Contoh: Arsip Keuangan 2025">
                        <small class="text-muted d-block mt-1">Isi keterangan secara singkat, misalnya <strong>"Arsip Keuangan 2025"</strong>.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL EDIT BOX ===================== --}}
<div class="modal fade" id="modalEditBox" tabindex="-1" aria-labelledby="modalEditBoxLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditBox" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditBoxLabel">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Box
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nomor_box" class="form-label" style="font-weight:bold">Nomor Box <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nomor_box" name="nomor_box" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_keterangan_box" class="form-label" style="font-weight:bold">Keterangan</label>
                        <input type="text" class="form-control" id="edit_keterangan_box" name="keterangan">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL HAPUS BOX ===================== --}}
<div class="modal fade" id="modalHapusBox" tabindex="-1" aria-labelledby="modalHapusBoxLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formHapusBox" action="" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="modalHapusBoxLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Box
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus box <strong id="hapusBoxNama"></strong>?</p>
                    <p class="text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Box yang memiliki arsip tidak dapat dihapus. Pindahkan atau hapus arsip terlebih dahulu.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== STYLES ===================== --}}
<style>
    .hover-card {
        transition: all 0.3s ease-in-out;
        cursor: pointer;
        position: relative;
        background: white;
    }
    .hover-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 1.5rem 2.5rem rgba(0,0,0,0.12) !important;
    }
    .hover-card:hover .card-top-border {
        height: 8px !important;
        transition: height 0.3s ease;
    }
    .hover-card:hover .icon-wrapper {
        transform: scale(1.1) rotate(5deg);
        transition: transform 0.3s ease;
    }
    .hover-card:hover .view-archive-btn {
        background-color: rgba(0,0,0,0.08) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .view-archive-btn {
        transition: all 0.3s ease;
    }
    .card-top-border {
        transition: height 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .card-top-border::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s ease;
    }
    .hover-card:hover .card-top-border::after {
        left: 100%;
    }
    .icon-wrapper {
        width: 85px;
        height: 85px;
        transition: all 0.3s ease;
    }
    .search-box .input-group-text,
    .search-box .form-control {
        background-color: #fff;
        border: 1px solid #dee2e6;
    }
    .search-box .form-control:focus {
        box-shadow: none;
        border-color: #198754;
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
        color: #198754;
    }
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
        color: #198754;
    }
    .box-card {
        animation: fadeInUp 0.5s ease backwards;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @for($i = 0; $i < 20; $i++)
    .box-card:nth-child({{ $i + 1 }}) {
        animation-delay: {{ $i * 0.05 }}s;
    }
    @endfor
    .badge {
        font-size: 0.85rem;
        font-weight: 500;
    }
    @media (max-width: 576px) {
        .card-body { padding: 1.25rem !important; }
        .icon-wrapper { width: 65px !important; height: 65px !important; }
        .icon-wrapper i { font-size: 2rem !important; }
        .card-title { font-size: 1.1rem; }
    }
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #198754; }
    .card-title { transition: transform 0.3s ease; }
    .hover-card:hover .card-title { transform: scale(1.02); }
    .btn-link {
        text-decoration: none;
    }
    .btn-link:hover {
        opacity: 0.7;
    }
</style>

{{-- ===================== JAVASCRIPT ===================== --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Filter Pencarian
        const searchInput = document.getElementById('searchBox');
        const clearBtn = document.getElementById('clearSearch');
        const cards = document.querySelectorAll('.box-card');
        const noResultDiv = document.getElementById('noResultMessage');

        function filterBox() {
            const keyword = searchInput.value.toLowerCase().trim();
            let hasVisible = false;
            cards.forEach((card, index) => {
                const boxName = card.getAttribute('data-box') || '';
                if (boxName.includes(keyword) || keyword === '') {
                    card.style.display = '';
                    setTimeout(() => {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(10px)';
                        setTimeout(() => {
                            card.style.transition = 'all 0.3s ease';
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 50);
                    }, index * 10);
                    hasVisible = true;
                } else {
                    card.style.display = 'none';
                }
            });
            if (!hasVisible && cards.length > 0) {
                noResultDiv.classList.remove('d-none');
                noResultDiv.style.opacity = '0';
                setTimeout(() => {
                    noResultDiv.style.transition = 'opacity 0.3s';
                    noResultDiv.style.opacity = '1';
                }, 50);
            } else {
                noResultDiv.classList.add('d-none');
            }
        }

        searchInput.addEventListener('keyup', filterBox);
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            filterBox();
            searchInput.focus();
        });

        // ================= MODAL EDIT BOX =================
        var editBoxModal = document.getElementById('modalEditBox');
        editBoxModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var nomor = button.getAttribute('data-nomor');
            var keterangan = button.getAttribute('data-keterangan') || '';
            var form = document.getElementById('formEditBox');
            form.action = '/manajemen-lokasi/box/' + id;
            document.getElementById('edit_nomor_box').value = nomor;
            document.getElementById('edit_keterangan_box').value = keterangan;
        });

        // ================= MODAL HAPUS BOX =================
        var hapusBoxModal = document.getElementById('modalHapusBox');
        hapusBoxModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var nomor = button.getAttribute('data-nomor');
            var form = document.getElementById('formHapusBox');
            form.action = '/manajemen-lokasi/box/' + id;
            document.getElementById('hapusBoxNama').textContent = nomor;
        });
    });
</script>
@endsection