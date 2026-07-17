@extends('layouts.app')

@section('page-title', 'Riwayat Pemindahan Arsip')
@section('page-subtitle', 'Daftar Arsip yang Telah Dipindahkan atau Ditolak')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-2"></i>Daftar Riwayat Pemindahan Arsip
                    </h3>
                </div>

                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('subbagian.riwayat-pemindahan.index') }}" class="form-inline">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <select name="status_pindah" class="form-control form-control-sm w-100">
                                            <option value="">Semua Status</option>
                                            <option value="DRAFT" {{ request('status_pindah') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                                            <option value="DIAJUKAN" {{ request('status_pindah') == 'DIAJUKAN' ? 'selected' : '' }}>Diajukan</option>
                                            <option value="DIPERBAIKI" {{ request('status_pindah') == 'DIPERBAIKI' ? 'selected' : '' }}>Diperbaiki</option>
                                            <option value="DITOLAK" {{ request('status_pindah') == 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
                                            <option value="DIPINDAHKAN" {{ request('status_pindah') == 'DIPINDAHKAN' ? 'selected' : '' }}>Dipindahkan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <select name="tahun" class="form-control form-control-sm w-100">
                                            <option value="">Semua Tahun</option>
                                            @foreach($tahunOptions as $tahun)
                                                <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <input type="text" name="search" class="form-control form-control-sm w-100"
                                               placeholder="Cari berdasarkan judul atau kode..."
                                               value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">
                                            <i class="fas fa-search mr-1"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 shadow-sm mb-4">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle fs-4 me-3 text-primary"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Informasi Menu Riwayat Pemindahan</h6>
                                <p class="mb-0">
                                    Menu ini menampilkan seluruh riwayat pemindahan arsip ke Unit Kearsipan,
                                    termasuk arsip yang masih dalam status <strong>DRAFT</strong>, sedang dalam proses (<strong>DIAJUKAN</strong>),
                                    <strong>DIPERBAIKI</strong>, <strong>DITOLAK</strong>, maupun yang sudah berhasil <strong>DIPINDAHKAN</strong>.
                                    Untuk arsip berstatus <strong>DITOLAK</strong>, klik <em>Perbaiki</em> untuk melihat alasan penolakan dan melakukan perbaikan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Riwayat -->
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light" style="position: sticky; top: 0; z-index: 1; background-color: #f8f9fa;">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="10%">Kode</th>
                                    <th width="45%">Judul Arsip</th>
                                    <th width="15%">Tanggal Pengajuan</th>
                                    <th width="15%">Status Pindah</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($arsips as $index => $arsip)
                                <tr>
                                    <td class="text-center">{{ ($arsips->currentPage() - 1) * $arsips->perPage() + $index + 1 }}</td>
                                    <td>
                                        <strong class="text-primary">{{ $arsip->kodeKlasifikasi->kode ?? '-' }}</strong>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold" style="font-size: 0.9rem;">
                                            {{ Str::limit($arsip->uraian_arsip, 80) }}
                                        </div>
                                        @if($arsip->tahun_arsip)
                                            <small class="text-muted">Tahun: {{ $arsip->tahun_arsip }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-nowrap">
                                            {{ $arsip->updated_at->format('d-m-Y') }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $arsip->updated_at->format('H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($arsip->status_pindah == 'DIPINDAHKAN')
                                            <span class="badge p-2" style="font-size: 0.8em; min-width: 100px; display: inline-block; text-align: center; color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb;">
                                                <i class="fas fa-check-circle mr-1"></i> DIPINDAHKAN
                                            </span>
                                        @elseif($arsip->status_pindah == 'DITOLAK')
                                            <span class="badge p-2" style="font-size: 0.8em; min-width: 100px; display: inline-block; text-align: center; color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb;">
                                                <i class="fas fa-times-circle mr-1"></i> DITOLAK
                                            </span>
                                        @elseif($arsip->status_pindah == 'DIAJUKAN')
                                            <span class="badge p-2" style="font-size: 0.8em; min-width: 100px; display: inline-block; text-align: center; color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba;">
                                                <i class="fas fa-clock mr-1"></i> DIAJUKAN
                                            </span>
                                        @elseif($arsip->status_pindah == 'DIPERBAIKI')
                                            <span class="badge p-2" style="font-size: 0.8em; min-width: 100px; display: inline-block; text-align: center; color: #0c5460; background-color: #d1ecf1; border: 1px solid #bee5eb;">
                                                <i class="fas fa-wrench mr-1"></i> DIPERBAIKI
                                            </span>
                                        @elseif($arsip->status_pindah == 'DRAFT')
                                            <span class="badge p-2" style="font-size: 0.8em; min-width: 100px; display: inline-block; text-align: center; color: #383d41; background-color: #e2e3e5; border: 1px solid #d6d8db;">
                                                <i class="fas fa-pencil-alt mr-1"></i> DRAFT
                                            </span>
                                        @else
                                            <span class="badge badge-secondary p-2" style="font-size: 0.8em; min-width: 100px; display: inline-block; text-align: center;">
                                                {{ $arsip->status_pindah }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Aksi">
                                            <a href="{{ route('subbagian.riwayat-pemindahan.show', $arsip->id) }}"
                                               class="btn btn-info" title="Detail">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>

                                            @if($arsip->status_pindah == 'DITOLAK')
                                                <a href="{{ route('subbagian.riwayat-pemindahan.perbaikan', $arsip->id) }}"
                                                   class="btn btn-warning" title="Lihat alasan penolakan & perbaiki arsip" style="margin-left: 10px">
                                                    <i class="bi bi-tools"></i> Perbaiki
                                                </a>
                                            @elseif($arsip->status_pindah == 'DIPERBAIKI')
                                                <a href="{{ route('subbagian.riwayat-pemindahan.perbaikan', $arsip->id) }}"
                                                   class="btn btn-primary" title="Lanjutkan perbaikan / ajukan kembali" style="margin-left: 10px">
                                                    <i class="bi bi-list-check"></i> Lanjutkan
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <h5>Tidak ada data riwayat pemindahan</h5>
                                            <p>Tidak ada arsip dengan status "Dipindahkan" atau "Diajukan" pada filter yang dipilih.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination tanpa tanda panah -->
                    @if ($arsips->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item {{ $arsips->onFirstPage() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $arsips->previousPageUrl() }}" rel="prev">
                                        Previous
                                    </a>
                                </li>

                                @foreach ($arsips->getUrlRange(1, $arsips->lastPage()) as $page => $url)
                                    <li class="page-item {{ $page == $arsips->currentPage() ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endforeach

                                <li class="page-item {{ !$arsips->hasMorePages() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $arsips->nextPageUrl() }}" rel="next">
                                        Next
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                Menampilkan {{ $arsips->firstItem() ?? 0 }} - {{ $arsips->lastItem() ?? 0 }} dari {{ $arsips->total() }} riwayat
                            </small>
                        </div>
                        <div class="col-md-6 text-right">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .badge {
        font-size: 0.8em;
        padding: 0.5em 1em;
        min-width: 100px;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,.04);
    }
    .btn-group .btn {
        font-size: 0.8rem;
    }
    .table th, .table td {
        vertical-align: middle !important;
    }
    .thead-light th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
    }
</style>
@endpush