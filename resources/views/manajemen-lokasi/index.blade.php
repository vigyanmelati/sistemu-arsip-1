@extends('layouts.app')

@section('page-title', 'Manajemen Lokasi')
@section('page-subtitle', 'Pilih Ruangan')

@section('content')
<div class="container-fluid px-4">
    {{-- Alert untuk arsip tanpa ruangan --}}
    @if($arsipTanpaRuangan > 0)
        <div class="alert alert-warning border-start border-4 border-warning shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-2" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                <span>
                    Terdapat <strong>{{ number_format($arsipTanpaRuangan) }}</strong> arsip yang belum memiliki ruangan.
                </span>
            </div>
            <a href="{{ route('arsip.index', ['filter' => 'tanpa_ruangan']) }}" class="btn btn-sm btn-warning">
                <i class="bi bi-archive"></i> Kelola Arsip
            </a>
        </div>
    @endif

    {{-- Header dan pencarian --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-semibold mb-0">
                <i class="bi bi-building me-2 text-danger"></i> Manajemen Lokasi
            </h3>
            <p class="text-muted mt-1 mb-0">Pilih ruangan untuk melihat detail rak dan arsip yang tersimpan.</p>
        </div>
        <div class="search-box" style="min-width: 250px;">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="searchRuangan" class="form-control border-start-0 ps-0" placeholder="Cari ruangan...">
                <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Reset pencarian">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Grid kartu ruangan --}}
    <div class="row g-4" id="ruanganGrid">
        @php
            // Urutkan ruangan berdasarkan abjad
            $ruanganList = collect($ruangans)->filter()->sort()->values()->all();

            // Fungsi mapping ikon berdasarkan nama ruangan
            $getIconClass = function($ruanganName) {
                $name = strtolower($ruanganName);
                $mapping = [
                    'rak'       => 'bi bi-grid-3x3-gap-fill',
                    'gudang'    => 'bi bi-box-seam-fill',
                    'kantor'    => 'bi bi-building-fill',
                    'arsip'     => 'bi bi-archive-fill',
                    'record'    => 'bi bi-database-fill',
                    'inaktif'   => 'bi bi-clock-history',
                    'ruang'     => 'bi bi-door-closed-fill',
                    'depan'     => 'bi bi-shop',
                    'belakang'  => 'bi bi-house-door-fill',
                    'lt1'       => 'bi bi-layers-fill',
                    'lt2'       => 'bi bi-layers-half',
                ];
                foreach ($mapping as $key => $icon) {
                    if (str_contains($name, $key)) {
                        return $icon;
                    }
                }
                return 'bi bi-building';
            };
        @endphp

        @forelse($ruanganList as $ruangan)
            @php
                $iconClass = $getIconClass($ruangan);
                // Warna unik berdasarkan hash nama ruangan (hue)
                $hash = abs(crc32($ruangan));
                $hue = $hash % 360;
                $bgOpacity = 0.12;
                $iconBgColor = "hsla({$hue}, 70%, 60%, {$bgOpacity})";
                $iconColor = "hsl({$hue}, 75%, 45%)";
            @endphp

            <div class="col-sm-6 col-lg-4 col-xl-3 ruangan-card" data-ruangan="{{ strtolower($ruangan) }}">
                <a href="{{ route('manajemen-lokasi.rak', $ruangan) }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm hover-card rounded-4 overflow-hidden">
                        <div class="card-body text-center p-4">
                            <div class="icon-wrapper rounded-circle d-inline-flex p-3 mb-3" 
                                 style="background-color: {{ $iconBgColor }}; transition: all 0.2s;">
                                <i class="{{ $iconClass }} fs-1" style="color: {{ $iconColor }};"></i>
                            </div>
                            <h5 class="card-title fw-semibold mb-2">
                                {{ $ruanganLabels[$ruangan] ?? ucwords(str_replace(['_', '-'], ' ', $ruangan)) }}
                            </h5>
                            <p class="card-text text-muted small mb-0">
                                <i class="bi bi-arrow-repeat me-1"></i> Kelola rak & arsip
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-center pb-3 pt-0">
                            <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                                <i class="bi bi-eye me-1"></i> Lihat rak
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center py-5 border-0 shadow-sm rounded-4">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    <h5 class="fw-semibold">Belum ada data ruangan</h5>
                    <p class="mb-0">Silakan <a href="{{ route('arsip.index') }}" class="alert-link">isi lokasi arsip</a> terlebih dahulu agar ruangan tersedia.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pesan jika pencarian tidak menemukan hasil --}}
    <div id="noResultMessage" class="text-center py-5 d-none">
        <i class="bi bi-search-slash fs-1 text-muted"></i>
        <h5 class="mt-3">Ruangan tidak ditemukan</h5>
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
        border-color: #dc3545;
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
        color: #dc3545;
    }
    /* Alert kustom */
    .alert-warning {
        background-color: #fff3e0;
        border-left-width: 5px !important;
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
    // Filter ruangan secara real-time
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchRuangan');
        const clearBtn = document.getElementById('clearSearch');
        const cards = document.querySelectorAll('.ruangan-card');
        const noResultDiv = document.getElementById('noResultMessage');

        function filterRuangan() {
            const keyword = searchInput.value.toLowerCase().trim();
            let hasVisible = false;

            cards.forEach(card => {
                const ruanganName = card.getAttribute('data-ruangan') || '';
                if (ruanganName.includes(keyword) || keyword === '') {
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

        searchInput.addEventListener('keyup', filterRuangan);
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            filterRuangan();
            searchInput.focus();
        });
    });
</script>
@endsection