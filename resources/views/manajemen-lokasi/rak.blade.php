@extends('layouts.app')

@section('page-title', 'Manajemen Lokasi - Rak')
@section('page-subtitle', 'Ruangan: ' . $ruanganLabel)

@section('content')
<div class="container-fluid px-4">
    {{-- Tombol kembali --}}
    <div class="mb-4">
        <a href="{{ route('manajemen-lokasi.index') }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Ruangan
        </a>
    </div>

    {{-- Header dan pencarian --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-semibold mb-0">
                <i class="bi bi-layers-fill me-2 text-primary"></i> Kelola Rak
            </h3>
            <p class="text-muted mt-1 mb-0">
                Ruangan: <strong>{{ $ruanganLabel }}</strong> — Pilih rak untuk melihat box dan arsip.
            </p>
        </div>
        <div class="search-box" style="min-width: 250px;">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="searchRak" class="form-control border-start-0 ps-0" placeholder="Cari rak...">
                <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Reset pencarian">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Grid kartu rak --}}
    <div class="row g-4" id="rakGrid">
        @php
            // Urutkan rak secara alami (misal: Rak 1, Rak 2, Rak 10)
            $raksSorted = collect($raks)->sort(function($a, $b) {
                return strnatcmp($a, $b);
            })->values()->all();

            // Fungsi untuk mendapatkan ikon berdasarkan nama rak
            $getIconClass = function($rakName) {
                $name = strtolower($rakName);
                if (str_contains($name, 'a')) return 'bi bi-bookmark-star-fill';
                if (str_contains($name, 'b')) return 'bi bi-bookmark-check-fill';
                if (str_contains($name, 'c')) return 'bi bi-bookmark-plus-fill';
                if (str_contains($name, '1')) return 'bi bi-1-square-fill';
                if (str_contains($name, '2')) return 'bi bi-2-square-fill';
                if (str_contains($name, '3')) return 'bi bi-3-square-fill';
                if (preg_match('/\d/', $name)) return 'bi bi-hash';
                return 'bi bi-layers-fill';
            };
        @endphp

        @forelse($raksSorted as $rak)
            @php
                $iconClass = $getIconClass($rak);
                // Warna unik berdasarkan hash nama rak (hue)
                $hash = abs(crc32($rak));
                $hue = $hash % 360;
                $bgOpacity = 0.12;
                $iconBgColor = "hsla({$hue}, 70%, 60%, {$bgOpacity})";
                $iconColor = "hsl({$hue}, 75%, 45%)";
            @endphp

            <div class="col-sm-6 col-lg-4 col-xl-3 rak-card" data-rak="{{ strtolower($rak) }}">
                <a href="{{ route('manajemen-lokasi.box', [$ruangan, $rak]) }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm hover-card rounded-4 overflow-hidden">
                        <div class="card-body text-center p-4">
                            <div class="icon-wrapper rounded-circle d-inline-flex p-3 mb-3" 
                                 style="background-color: {{ $iconBgColor }}; transition: all 0.2s;">
                                <i class="{{ $iconClass }} fs-1" style="color: {{ $iconColor }};"></i>
                            </div>
                            <h5 class="card-title fw-semibold mb-2">Rak {{ $rak }}</h5>
                            <p class="card-text text-muted small mb-0">
                                <i class="bi bi-box-seam me-1"></i> Kelola box & arsip
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-center pb-3 pt-0">
                            <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                                <i class="bi bi-eye me-1"></i> Lihat box
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center py-5 border-0 shadow-sm rounded-4">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    <h5 class="fw-semibold">Belum ada rak di ruangan ini</h5>
                    <p class="mb-0">Silakan tambah arsip terlebih dahulu untuk membuat rak.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pesan jika pencarian tidak menemukan hasil --}}
    <div id="noResultMessage" class="text-center py-5 d-none">
        <i class="bi bi-search-slash fs-1 text-muted"></i>
        <h5 class="mt-3">Rak tidak ditemukan</h5>
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
        border-color: #0d6efd;
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
        color: #0d6efd;
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
    // Filter rak secara real-time
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchRak');
        const clearBtn = document.getElementById('clearSearch');
        const cards = document.querySelectorAll('.rak-card');
        const noResultDiv = document.getElementById('noResultMessage');

        function filterRak() {
            const keyword = searchInput.value.toLowerCase().trim();
            let hasVisible = false;

            cards.forEach(card => {
                const rakName = card.getAttribute('data-rak') || '';
                if (rakName.includes(keyword) || keyword === '') {
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

        searchInput.addEventListener('keyup', filterRak);
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            filterRak();
            searchInput.focus();
        });
    });
</script>
@endsection