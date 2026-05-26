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
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('subbagian.riwayat-pemindahan.index') }}" class="form-inline">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <select name="status_pindah" class="form-control form-control-sm w-100">
                                            <option value="">Semua Status</option>
                                            <option value="DIPINDAHKAN" {{ request('status_pindah') == 'DIPINDAHKAN' ? 'selected' : '' }}>Dipindahkan</option>
                                            <option value="DIAJUKAN" {{ request('status_pindah') == 'DIAJUKAN' ? 'selected' : '' }}>Diajukan</option>
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
                            <i class="bi bi-info-circle-fill fs-4 me-3 text-primary"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Informasi Menu Riwayat Pemindahan</h6>
                                <p class="mb-0">
                                    Menu ini berisi informasi riwayat pemindahan arsip ke Unit Kearsipan, 
                                    termasuk arsip yang sedang dalam proses pemindahan maupun arsip yang telah berhasil dipindahkan.
                                    Pengguna dapat memantau status terbaru dari setiap proses pemindahan arsip.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <!-- <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        Halaman ini menampilkan riwayat pemindahan arsip.
                        Untuk arsip dengan status <strong>DITOLAK</strong>, tersedia opsi untuk melakukan perbaikan dan pengajuan kembali.
                    </div> -->

                    <!-- Tabel Riwayat -->
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light" style="position: sticky; top: 0; z-index: 1; background-color: #f8f9fa;">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="10%">Kode</th>
                                    <!-- <th width="15%">Klasifikasi</th> -->
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
                                    <!-- <td>
                                        @if($arsip->kodeKlasifikasi)
                                            <small class="text-muted">{{ Str::limit($arsip->kodeKlasifikasi->uraian ?? '-', 50) }}</small>
                                        @else
                                            -
                                        @endif
                                    </td> -->
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
                                            <span class="badge badge-success p-2" style="font-size: 0.8em; min-width: 100px; display: inline-block; text-align: center;color:green">
                                                <i class="fas fa-check-circle mr-1"></i> DIPINDAHKAN
                                            </span>
                                        @elseif($arsip->status_pindah == 'DITOLAK')
                                            <span class="badge badge-danger p-2" style="font-size: 0.8em; min-width: 100px; display: inline-block; text-align: center;color:red">
                                                <i class="fas fa-times-circle mr-1"></i> DITOLAK
                                            </span>
                                        @else
                                            <span class="badge badge-secondary p-2" style="font-size: 0.8em; min-width: 100px; display: inline-block; text-align: center;COLOR:ORANGE">
                                                {{ $arsip->status_pindah }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group-vertical btn-group-sm" role="group" aria-label="Aksi">
                                            <a href="{{ route('subbagian.riwayat-pemindahan.show', $arsip->id) }}" 
                                               class="btn btn-info mb-1" title="Detail">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            
                                            @if($arsip->status_pindah == 'DITOLAK')
                                                <button type="button" class="btn btn-warning mb-1" 
                                                        data-toggle="modal" data-target="#modalPerbaikan{{ $arsip->id }}"
                                                        title="Perbaikan">
                                                    <i class="fas fa-wrench"></i> Perbaikan
                                                </button>
                                                
                                                <button type="button" class="btn btn-primary" 
                                                        data-toggle="modal" data-target="#modalAjukanKembali{{ $arsip->id }}"
                                                        title="Ajukan Kembali">
                                                    <i class="fas fa-redo"></i> Ajukan
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Modal Perbaikan -->
                                        <div class="modal fade" id="modalPerbaikan{{ $arsip->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('subbagian.riwayat-pemindahan.perbaiki', $arsip->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Perbaikan Arsip</h5>
                                                            <button type="button" class="close" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label>Kode: <strong>{{ $arsip->kodeKlasifikasi->kode ?? '-' }}</strong></label>
                                                                <p class="text-muted">{{ Str::limit($arsip->uraian_arsip, 100) }}</p>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="catatan_perbaikan">Catatan Perbaikan *</label>
                                                                <textarea name="catatan_perbaikan" id="catatan_perbaikan" 
                                                                          class="form-control" rows="4" required
                                                                          placeholder="Jelaskan perbaikan yang telah dilakukan..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-warning">
                                                                <i class="fas fa-wrench mr-1"></i> Tandai sebagai Diperbaiki
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Ajukan Kembali -->
                                        <div class="modal fade" id="modalAjukanKembali{{ $arsip->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('subbagian.riwayat-pemindahan.ajukan-kembali', $arsip->id) }}" 
                                                          method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Ajukan Kembali Arsip</h5>
                                                            <button type="button" class="close" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label>Kode: <strong>{{ $arsip->kodeKlasifikasi->kode ?? '-' }}</strong></label>
                                                                <p class="text-muted">{{ Str::limit($arsip->uraian_arsip, 100) }}</p>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="file_berita_acara_baru">Upload Berita Acara Baru *</label>
                                                                <input type="file" name="file_berita_acara_baru" 
                                                                       id="file_berita_acara_baru" class="form-control" required>
                                                                <small class="form-text text-muted">
                                                                    Format: PDF, JPG, JPEG, PNG (Maks: 2MB)
                                                                </small>
                                                            </div>
                                                            @if($arsip->catatan_perbaikan)
                                                            <div class="form-group">
                                                                <label>Catatan Perbaikan:</label>
                                                                <div class="alert alert-light">
                                                                    {{ $arsip->catatan_perbaikan }}
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="fas fa-redo mr-1"></i> Ajukan Kembali
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
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

                    <!-- Pagination -->
                    <!-- <div class="d-flex justify-content-center mt-3">
                        {{ $arsips->withQueryString()->links() }}
                    </div> -->
                    <!-- Pagination tanpa tanda panah -->
@if ($arsips->hasPages())
<div class="d-flex justify-content-center mt-3">
    <nav aria-label="Page navigation">
        <ul class="pagination pagination-sm mb-0">
            {{-- Tombol Previous tanpa panah --}}
            <li class="page-item {{ $arsips->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $arsips->previousPageUrl() }}" rel="prev">
                    Previous
                </a>
            </li>

            {{-- Nomor halaman (opsional, bisa dihilangkan jika hanya ingin Prev/Next) --}}
            @foreach ($arsips->getUrlRange(1, $arsips->lastPage()) as $page => $url)
                <li class="page-item {{ $page == $arsips->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
            @endforeach

            {{-- Tombol Next tanpa panah --}}
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
                            <!-- <small class="text-muted">
                                © {{ date('Y') }} SINAR - KPU Provinsi Bali v1.0.0
                            </small> -->
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
    .btn-group-vertical .btn {
        margin-bottom: 3px;
        padding: 0.25rem 0.5rem;
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