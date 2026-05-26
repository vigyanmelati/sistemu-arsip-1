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

     <div class="alert alert-info border-0 shadow-sm mb-4">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle-fill fs-4 me-3 text-primary"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Informasi Menu Manajemen Lokasi</h6>
                                <p class="mb-0">
                                     Menu ini digunakan untuk melihat arsip berdasarkan lokasi keberadaannya. 
            Pengguna dapat memilih ruangan untuk menampilkan daftar rak, box  serta arsip yang tersimpan pada lokasi tersebut.
                                </p>
                            </div>
                        </div>
                    </div>

    {{-- Grid kartu ruangan --}}
    <div class="row g-4" id="ruanganGrid">
        @php
            // Urutkan ruangan berdasarkan abjad
            $ruanganList = collect($ruangans)->filter()->sort()->values()->all();

            // Warna gradien untuk border atas
            $gradientColors = [
                'linear-gradient(90deg, #dc3545, #f87171)',
                'linear-gradient(90deg, #0d6efd, #0dcaf0)',
                'linear-gradient(90deg, #198754, #20c997)',
                'linear-gradient(90deg, #fd7e14, #ffc107)',
                'linear-gradient(90deg, #6f42c1, #a78bfa)',
                'linear-gradient(90deg, #d63384, #f472b6)',
                'linear-gradient(90deg, #20c997, #198754)',
                'linear-gradient(90deg, #0dcaf0, #0d6efd)'
            ];
            
            // Warna icon
            $iconColors = [
                '#dc3545', '#0d6efd', '#198754', '#fd7e14', 
                '#6f42c1', '#d63384', '#20c997', '#0dcaf0'
            ];
            
            // Background colors yang soft
            $bgColors = [
                'rgba(220, 53, 69, 0.1)',
                'rgba(13, 110, 253, 0.1)',
                'rgba(25, 135, 84, 0.1)',
                'rgba(253, 126, 20, 0.1)',
                'rgba(111, 66, 193, 0.1)',
                'rgba(214, 51, 132, 0.1)',
                'rgba(32, 201, 151, 0.1)',
                'rgba(13, 202, 240, 0.1)'
            ];

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

        @forelse($ruanganList as $index => $ruangan)
            @php
                $iconClass = $getIconClass($ruangan);
                $iconColor = $iconColors[$index % count($iconColors)];
                $bgColor = $bgColors[$index % count($bgColors)];
                $gradientBorder = $gradientColors[$index % count($gradientColors)];
                $displayName = $ruanganLabels[$ruangan] ?? ucwords(str_replace(['_', '-'], ' ', $ruangan));
            @endphp

            <div class="col-sm-6 col-lg-4 col-xl-3 ruangan-card" data-ruangan="{{ strtolower($ruangan) }}">
                <a href="{{ route('manajemen-lokasi.rak', $ruangan) }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm hover-card rounded-4 overflow-hidden">
                        <!-- Border atas gradien -->
                        <div class="card-top-border" style="height: 6px; background: {{ $gradientBorder }}; width: 100%;"></div>
                        
                        <div class="card-body text-center p-4">
                            <!-- Icon Ruangan -->
                            <div class="icon-wrapper rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                 style="width: 85px; height: 85px; background: {{ $bgColor }}; transition: all 0.3s;">
                                <i class="{{ $iconClass }} fs-1" style="color: {{ $iconColor }}; font-size: 3rem !important;"></i>
                            </div>
                            
                            <h5 class="card-title fw-semibold mb-2" style="color: {{ $iconColor }};">{{ $displayName }}</h5>
                            
                            <!-- Badge informasi -->
                            <div class="mb-3">
                                <span class="badge px-3 py-2 rounded-pill" style="background: {{ $bgColor }}; color: {{ $iconColor }}; font-weight: 500;">
                                    <i class="bi bi-arrow-repeat me-1"></i> Kelola rak & arsip
                                </span>
                            </div>
                            
                            <!-- Tombol Lihat Rak -->
                            <div class="d-grid gap-2">
                                <span class="btn btn-sm view-rak-btn" 
                                      style="border: 2px solid {{ $iconColor }}; color: {{ $iconColor }}; background: white; border-radius: 50px; font-weight: 500; transition: all 0.3s;">
                                    <i class="bi bi-eye me-1"></i> Lihat Rak
                                </span>
                            </div>
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
        transition: all 0.3s ease-in-out;
        cursor: pointer;
        position: relative;
        background: white;
    }
    
    .hover-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 1.5rem 2.5rem rgba(0,0,0,0.12) !important;
    }
    
    /* Efek border atas saat hover */
    .hover-card:hover .card-top-border {
        height: 8px !important;
        transition: height 0.3s ease;
    }
    
    .hover-card:hover .icon-wrapper {
        transform: scale(1.1) rotate(5deg);
        transition: transform 0.3s ease;
    }
    
    /* Hover tombol - Background berubah tapi TEKS TETAP WARNA ASLI */
    .hover-card:hover .view-rak-btn {
        background-color: rgba(0,0,0,0.05) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    /* Efek animasi garis pada border */
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
    
    /* Animasi untuk kartu */
    .ruangan-card {
        animation: fadeInUp 0.5s ease backwards;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Memberikan delay animasi untuk setiap kartu */
    @for($i = 0; $i < 20; $i++)
    .ruangan-card:nth-child({{ $i + 1 }}) {
        animation-delay: {{ $i * 0.05 }}s;
    }
    @endfor
    
    /* Badge styling */
    .badge {
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    /* Responsif */
    @media (max-width: 576px) {
        .card-body {
            padding: 1.25rem !important;
        }
        .icon-wrapper {
            width: 65px !important;
            height: 65px !important;
        }
        .icon-wrapper i {
            font-size: 2rem !important;
        }
        .card-title {
            font-size: 1.1rem;
        }
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #dc3545;
    }
    
    /* Efek tambahan untuk teks */
    .card-title {
        transition: transform 0.3s ease;
    }
    
    .hover-card:hover .card-title {
        transform: scale(1.02);
    }
</style>

<script>
    // Filter ruangan secara real-time dengan animasi
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchRuangan');
        const clearBtn = document.getElementById('clearSearch');
        const cards = document.querySelectorAll('.ruangan-card');
        const noResultDiv = document.getElementById('noResultMessage');

        function filterRuangan() {
            const keyword = searchInput.value.toLowerCase().trim();
            let hasVisible = false;

            cards.forEach((card, index) => {
                const ruanganName = card.getAttribute('data-ruangan') || '';
                if (ruanganName.includes(keyword) || keyword === '') {
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

        searchInput.addEventListener('keyup', filterRuangan);
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            filterRuangan();
            searchInput.focus();
        });
    });
</script>
@endsection