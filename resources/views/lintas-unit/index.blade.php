@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="mb-4">
        <h3 class="fw-bold mb-1">
            <i class="bi bi-diagram-3-fill text-primary"></i>
            Penelusuran Arsip Antar Unit
        </h3>

        <p class="text-muted mb-0">
            Pilih sub bagian untuk melihat persebaran dan lokasi arsip.
        </p>
    </div>

    {{-- CARD UNIT --}}
    <div class="row g-4">

        {{-- UNIT KEARSIPAN --}}
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('lintas-unit.daftar', 'unit-kearsipan') }}"
               class="text-decoration-none">

                <div class="card border-0 shadow-sm h-100 card-unit">
                    <div class="card-body">

                        <div class="d-flex align-items-center mb-3">

                            <div class="icon-box bg-primary-subtle text-primary">
                                <i class="bi bi-archive-fill"></i>
                            </div>

                            <div class="ms-3">
                                <h5 class="fw-bold mb-0">
                                    Unit Kearsipan
                                </h5>

                                <small class="text-muted">
                                    Pusat pengelolaan arsip
                                </small>
                            </div>

                        </div>

                        <hr>

                        <div class="small text-muted">

                            <div class="mb-2">
                                <i class="bi bi-folder2-open"></i>
                                Arsip aktif & inaktif
                            </div>

                            <div class="mb-2">
                                <i class="bi bi-shield-check"></i>
                                Retensi arsip
                            </div>

                            <div>
                                <i class="bi bi-arrow-left-right"></i>
                                Pemindahan arsip
                            </div>

                        </div>

                    </div>
                </div>
            </a>
        </div>

        {{-- SUB BAGIAN UMUM DAN LOGISTIK --}}
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('lintas-unit.daftar', 'subbag-umum-logistik') }}"
               class="text-decoration-none">

                <div class="card border-0 shadow-sm h-100 card-unit">
                    <div class="card-body">

                        <div class="d-flex align-items-center mb-3">

                            <div class="icon-box bg-primary-subtle text-primary">
                                <i class="bi bi-building-fill"></i>
                            </div>

                            <div class="ms-3">
                                <h5 class="fw-bold mb-0">
                                    Sub Bagian Umum dan Logistik
                                </h5>

                                <small class="text-muted">
                                    Administrasi & logistik
                                </small>
                            </div>

                        </div>

                        <hr>

                        <div class="small text-muted">

                            <div class="mb-2">
                                <i class="bi bi-box-seam"></i>
                                Arsip logistik
                            </div>

                            <div class="mb-2">
                                <i class="bi bi-file-earmark-text"></i>
                                Surat & administrasi
                            </div>

                            <div>
                                <i class="bi bi-truck"></i>
                                Distribusi barang
                            </div>

                        </div>

                    </div>
                </div>
            </a>
        </div>

        {{-- SUB BAGIAN PARTISIPASI --}}
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('lintas-unit.daftar', 'subbag-partisipasi') }}"
               class="text-decoration-none">

                <div class="card border-0 shadow-sm h-100 card-unit">
                    <div class="card-body">

                        <div class="d-flex align-items-center mb-3">

                            <div class="icon-box bg-success-subtle text-success">
                                <i class="bi bi-people-fill"></i>
                            </div>

                            <div class="ms-3">
                                <h5 class="fw-bold mb-0">
                                    Sub Bagian Partisipasi, Hubungan Masyarakat dan SDM
                                </h5>

                                <small class="text-muted">
                                    Humas & SDM
                                </small>
                            </div>

                        </div>

                        <hr>

                        <div class="small text-muted">

                            <div class="mb-2">
                                <i class="bi bi-megaphone-fill"></i>
                                Dokumentasi publikasi
                            </div>

                            <div class="mb-2">
                                <i class="bi bi-person-badge-fill"></i>
                                Arsip kepegawaian
                            </div>

                            <div>
                                <i class="bi bi-camera-video-fill"></i>
                                Arsip media & dokumentasi
                            </div>

                        </div>

                    </div>
                </div>
            </a>
        </div>

        {{-- SUB BAGIAN KEUANGAN --}}
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('lintas-unit.daftar', 'subbag-keuangan') }}"
               class="text-decoration-none">

                <div class="card border-0 shadow-sm h-100 card-unit">
                    <div class="card-body">

                        <div class="d-flex align-items-center mb-3">

                            <div class="icon-box bg-warning-subtle text-warning">
                                <i class="bi bi-cash-stack"></i>
                            </div>

                            <div class="ms-3">
                                <h5 class="fw-bold mb-0">
                                    Sub Bagian Keuangan
                                </h5>

                                <small class="text-muted">
                                    Pengelolaan anggaran
                                </small>
                            </div>

                        </div>

                        <hr>

                        <div class="small text-muted">

                            <div class="mb-2">
                                <i class="bi bi-wallet2"></i>
                                SPJ & pembayaran
                            </div>

                            <div class="mb-2">
                                <i class="bi bi-receipt"></i>
                                Dokumen anggaran
                            </div>

                            <div>
                                <i class="bi bi-journal-check"></i>
                                Arsip laporan keuangan
                            </div>

                        </div>

                    </div>
                </div>
            </a>
        </div>

        {{-- SUB BAGIAN PERENCANAAN --}}
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('lintas-unit.daftar', 'subbag-perencanaan') }}"
               class="text-decoration-none">

                <div class="card border-0 shadow-sm h-100 card-unit">
                    <div class="card-body">

                        <div class="d-flex align-items-center mb-3">

                            <div class="icon-box bg-info-subtle text-info">
                                <i class="bi bi-bar-chart-fill"></i>
                            </div>

                            <div class="ms-3">
                                <h5 class="fw-bold mb-0">
                                    Sub Bagian Perencanaan, Data, dan Informasi
                                </h5>

                                <small class="text-muted">
                                    Data & perencanaan
                                </small>
                            </div>

                        </div>

                        <hr>

                        <div class="small text-muted">

                            <div class="mb-2">
                                <i class="bi bi-database-fill"></i>
                                Arsip data pemilu
                            </div>

                            <div class="mb-2">
                                <i class="bi bi-clipboard-data-fill"></i>
                                Dokumen perencanaan
                            </div>

                            <div>
                                <i class="bi bi-pie-chart-fill"></i>
                                Statistik & laporan
                            </div>

                        </div>

                    </div>
                </div>
            </a>
        </div>

        {{-- SUB BAGIAN TEKNIS --}}
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('lintas-unit.daftar', 'subbag-teknis') }}"
               class="text-decoration-none">

                <div class="card border-0 shadow-sm h-100 card-unit">
                    <div class="card-body">

                        <div class="d-flex align-items-center mb-3">

                            <div class="icon-box bg-secondary-subtle text-secondary">
                                <i class="bi bi-gear-wide-connected"></i>
                            </div>

                            <div class="ms-3">
                                <h5 class="fw-bold mb-0">
                                    Sub Bagian Teknis Penyelenggaraan Pemilu
                                </h5>

                                <small class="text-muted">
                                    Teknis penyelenggaraan
                                </small>
                            </div>

                        </div>

                        <hr>

                        <div class="small text-muted">

                            <div class="mb-2">
                                <i class="bi bi-check2-square"></i>
                                Dokumen tahapan pemilu
                            </div>

                            <div class="mb-2">
                                <i class="bi bi-file-earmark-ruled"></i>
                                Arsip teknis pemilu
                            </div>

                            <div>
                                <i class="bi bi-list-task"></i>
                                Administrasi kegiatan
                            </div>

                        </div>

                    </div>
                </div>
            </a>
        </div>

        {{-- SUB BAGIAN HUKUM --}}
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('lintas-unit.daftar', 'subbag-hukum') }}"
               class="text-decoration-none">

                <div class="card border-0 shadow-sm h-100 card-unit">
                    <div class="card-body">

                        <div class="d-flex align-items-center mb-3">

                            <div class="icon-box bg-danger-subtle text-danger">
                                <i class="bi bi-bank2"></i>
                            </div>

                            <div class="ms-3">
                                <h5 class="fw-bold mb-0">
                                    Sub Bagian Hukum
                                </h5>

                                <small class="text-muted">
                                    Regulasi & dokumentasi hukum
                                </small>
                            </div>

                        </div>

                        <hr>

                        <div class="small text-muted">

                            <div class="mb-2">
                                <i class="bi bi-journal-text"></i>
                                Produk hukum
                            </div>

                            <div class="mb-2">
                                <i class="bi bi-file-earmark-lock"></i>
                                Dokumen sengketa
                            </div>

                            <div>
                                <i class="bi bi-journal-bookmark-fill"></i>
                                Arsip Regulasi
                            </div>

                        </div>

                    </div>
                </div>
            </a>
        </div>

    </div>

</div>

<style>
.card-unit{
    transition: .3s ease;
    border-radius: 18px;
}

.card-unit:hover{
    transform: translateY(-6px);
    box-shadow: 0 14px 35px rgba(0,0,0,0.08)!important;
}

.icon-box{
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size: 24px;
}

.card-unit h5{
    font-size: 17px;
    line-height: 1.4;
}
</style>
@endsection