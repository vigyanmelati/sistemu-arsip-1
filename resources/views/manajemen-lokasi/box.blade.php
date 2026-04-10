@extends('layouts.app')

@section('page-title', 'Manajemen Lokasi - Box')
@section('page-subtitle', 'Ruangan: ' . $ruanganLabel . ' | Rak: ' . $rak)

@section('content')
<div class="container-fluid px-4">
    {{-- Breadcrumb navigasi --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('manajemen-lokasi.index') }}" class="text-decoration-none">Ruangan</a></li>
            <li class="breadcrumb-item"><a href="{{ route('manajemen-lokasi.rak', $ruangan) }}" class="text-decoration-none">{{ $ruanganLabel }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $rak }}</li>
        </ol>
    </nav>

    {{-- Tombol kembali --}}
    <div class="mb-4">
        <a href="{{ route('manajemen-lokasi.rak', $ruangan) }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Rak
        </a>
    </div>

    {{-- Header dan pencarian --}}
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

    {{-- Grid kartu box --}}
    <div class="row g-4" id="boxGrid">
        @php
            // Urutkan box secara alami (misal: Box 1, Box 2, Box 10)
            $boxesSorted = collect($boxes)->sort(function($a, $b) {
                return strnatcmp($a, $b);
            })->values()->all();

            // Fungsi untuk mendapatkan ikon berdasarkan nama box
            $getIconClass = function($boxName) {
                $name = strtolower($boxName);
                if (str_contains($name, 'box')) return 'bi bi-box-seam-fill';
                if (str_contains($name, 'folder')) return 'bi bi-folder-fill';
                if (str_contains($name, 'dus')) return 'bi bi-archive-fill';
                if (str_contains($name, 'kardus')) return 'bi bi-card-list';
                if (preg_match('/\d/', $name)) return 'bi bi-hash';
                if (str_contains($name, 'a')) return 'bi bi-bookmark-star-fill';
                if (str_contains($name, 'b')) return 'bi bi-bookmark-check-fill';
                if (str_contains($name, 'c')) return 'bi bi-bookmark-plus-fill';
                return 'bi bi-archive';
            };
        @endphp

        @forelse($boxesSorted as $box)
            @php
                $iconClass = $getIconClass($box);
                // Warna unik berdasarkan hash nama box (hue)
                $hash = abs(crc32($box));
                $hue = $hash % 360;
                $bgOpacity = 0.12;
                $iconBgColor = "hsla({$hue}, 70%, 60%, {$bgOpacity})";
                $iconColor = "hsl({$hue}, 75%, 45%)";
            @endphp

            <div class="col-sm-6 col-lg-4 col-xl-3 box-card" data-box="{{ strtolower($box) }}">
                <a href="{{ route('manajemen-lokasi.arsip', [$ruangan, $rak, $box]) }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm hover-card rounded-4 overflow-hidden">
                        <div class="card-body text-center p-4">
                            <div class="icon-wrapper rounded-circle d-inline-flex p-3 mb-3" 
                                 style="background-color: {{ $iconBgColor }}; transition: all 0.2s;">
                                <i class="{{ $iconClass }} fs-1" style="color: {{ $iconColor }};"></i>
                            </div>
                            <h5 class="card-title fw-semibold mb-2">{{ $box }}</h5>
                            <p class="card-text text-muted small mb-0">
                                <i class="bi bi-files me-1"></i> Kelola arsip
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-center pb-3 pt-0">
                            <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                                <i class="bi bi-eye me-1"></i> Lihat arsip
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center py-5 border-0 shadow-sm rounded-4">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    <h5 class="fw-semibold">Belum ada box di rak ini</h5>
                    <p class="mb-0">Silakan tambah arsip terlebih dahulu untuk membuat box.</p>
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

<style>
    /* Efek hover kartu */
    .hover-card {
        transition: all 0.25s ease-in-out;
        cursor: pointer;
    }
    .hover-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 1rem 2rem rgba(0,0,0,0.1) !important;
    }
    .hover-card:hover .icon-wrapper {
        transform: scale(1.08);
        transition: transform 0.2s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .icon-wrapper {
        width: 64px;
        height: 64px;
        transition: all 0.2s;
    }
    /* Search box styling */
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
    /* Breadcrumb styling */
    .breadcrumb-item a {
        color: #0d6efd;
    }
    .breadcrumb-item.active {
        font-weight: 600;
        color: #6c757d;
    }
    /* Responsif */
    @media (max-width: 576px) {
        .card-body {
            padding: 1.25rem !important;
        }
        .icon-wrapper {
            width: 52px;
            height: 52px;
        }
        .icon-wrapper i {
            font-size: 1.75rem !important;
        }
    }
</style>

<script>
    // Filter box secara real-time
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchBox');
        const clearBtn = document.getElementById('clearSearch');
        const cards = document.querySelectorAll('.box-card');
        const noResultDiv = document.getElementById('noResultMessage');

        function filterBox() {
            const keyword = searchInput.value.toLowerCase().trim();
            let hasVisible = false;

            cards.forEach(card => {
                const boxName = card.getAttribute('data-box') || '';
                if (boxName.includes(keyword) || keyword === '') {
                    card.style.display = '';
                    hasVisible = true;
                } else {
                    card.style.display = 'none';
                }
            });

            if (!hasVisible && cards.length > 0) {
                noResultDiv.classList.remove('d-none');
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
    });
</script>
@endsection