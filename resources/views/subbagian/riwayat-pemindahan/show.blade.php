@extends('layouts.app')

@section('page-title', 'Detail Riwayat Pemindahan')
@section('page-subtitle', 'Informasi Lengkap Arsip')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>Detail Arsip
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('subbagian.riwayat-pemindahan.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Informasi Utama -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h4 class="card-title mb-0">Informasi Arsip</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="30%">Kode Klasifikasi</th>
                                            <td>{{ $arsip->kodeKlasifikasi->kode ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Uraian Klasifikasi</th>
                                            <td>{{ $arsip->kodeKlasifikasi->uraian ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Uraian Arsip</th>
                                            <td>{{ $arsip->uraian_arsip }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tahun Arsip</th>
                                            <td>{{ $arsip->tahun_arsip }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Arsip</th>
                                            <td>{{ $arsip->tanggal_arsip }}</td>
                                        </tr>
                                        <tr>
                                            <th>Jumlah Berkas</th>
                                            <td>{{ $arsip->jumlah_berkas }} {{ $arsip->satuan_arsip }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Status dan Aksi -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h4 class="card-title mb-0">Status Pemindahan</h4>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-4">
                                        @if($arsip->status_pindah == 'DIPINDAHKAN')
                                            <div class="badge badge-success p-3" style="font-size: 1.2em; color:green">
                                                DIPINDAHKAN
                                            </div>
                                            <p class="mt-2 text-muted">
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                {{ $arsip->updated_at->format('d F Y H:i') }}
                                            </p>
                                        @elseif($arsip->status_pindah == 'DITOLAK')
                                            <div class="badge badge-danger p-3" style="font-size: 1.2em;color:red">
                                                DITOLAK
                                            </div>
                                            <p class="mt-2 text-muted">
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                {{ $arsip->updated_at->format('d F Y H:i') }}
                                            </p>
                                        @endif
                                    </div>

                                    @if($arsip->file_berita_acara)
                                    <div class="mb-3">
                                        <h6>Berita Acara</h6>
                                        <a href="{{ asset('storage/arsip/' . $arsip->file_berita_acara) }}" 
                                           target="_blank" class="btn btn-sm btn-outline-primary btn-block">
                                            <i class="fas fa-file-pdf mr-1"></i> Lihat Berita Acara
                                        </a>
                                    </div>
                                    @endif

                                    <!-- Update bagian Aksi -->
                                    @if($arsip->status_pindah == 'DITOLAK')
                                    <div class="mt-3">
                                        <h6>Aksi</h6>
                                        <a href="{{ route('subbagian.riwayat-pemindahan.edit-perbaikan', $arsip->id) }}" 
                                        class="btn btn-warning btn-sm btn-block mb-2">
                                            <i class="fas fa-wrench mr-1"></i> Edit Arsip & Upload BA Baru
                                        </a>
                                    </div>
                                    @endif

                                    @if($arsip->status_pindah == 'DIPERBAIKI')
                                    <div class="mt-3">
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-info-circle mr-2"></i>Arsip Sudah Diperbaiki</h6>
                                            <p class="mb-0">{{ $arsip->catatan_perbaikan }}</p>
                                        </div>
                                        
                                        <form action="{{ route('subbagian.riwayat-pemindahan.ajukan-kembali', $arsip->id) }}" 
                                            method="POST" onsubmit="return confirm('Ajukan kembali arsip untuk verifikasi?')">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm btn-block">
                                                <i class="fas fa-redo mr-1"></i> Ajukan Kembali
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                                                    </div>
                            </div>

                            @if($arsip->status_pindah == 'DITOLAK' && $arsip->catatan_verifikasi)
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="card border-danger">
                                            <div class="card-header bg-danger text-white">
                                                <h5 class="card-title mb-0">
                                                    <i class="fas fa-exclamation-triangle mr-2"></i>Alasan Penolakan
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-9">
                                                        <p class="mb-1"><strong>Keterangan:</strong></p>
                                                        <p class="text-danger">{{ $arsip->catatan_verifikasi }}</p>
                                                    </div>
                                                    <div class="col-md-3">
                                                        @if($arsip->diverifikasi_oleh && $arsip->tanggal_diverifikasi)
                                                        <p class="mb-1"><strong>Diverifikasi oleh:</strong></p>
                                                        <p>{{ $arsip->verifikator->name ?? 'Admin' }}</p>
                                                        <p class="mb-1"><strong>Tanggal:</strong></p>
                                                        <p>{{ \Carbon\Carbon::parse($arsip->tanggal_diverifikasi)->format('d F Y H:i') }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                        </div>
                    </div>

                    <!-- Informasi Tambahan -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h4 class="card-title mb-0">Informasi Tambahan</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h6>Lokasi Penyimpanan</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <th>Nomor Rak</th>
                                                    <td>{{ $arsip->nomor_rak ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Nomor Box</th>
                                                    <td>{{ $arsip->nomor_box ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Nomor Sampul</th>
                                                    <td>{{ $arsip->nomor_sampul ?? '-' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Kondisi Arsip</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <th>Tingkat Perkembangan</th>
                                                    <td>{{ $arsip->tingkat_perkembangan ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Keterangan</th>
                                                    <td>{{ $arsip->keterangan ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Media Arsip</th>
                                                    <td>{{ $arsip->media_arsip ?? '-' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Informasi Sistem</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <th>Sub Bagian</th>
                                                    <td>{{ $arsip->subBagian->nama_sub_bagian ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Tanggal Masuk</th>
                                                    <td>{{ $arsip->tanggal_masuk }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Dibuat Oleh</th>
                                                    <td>{{ $arsip->createdBy->name ?? '-' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    @if($arsip->catatan_perbaikan)
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="alert alert-warning">
                                                <h6><i class="fas fa-exclamation-triangle mr-2"></i>Catatan Perbaikan</h6>
                                                <p class="mb-0">{{ $arsip->catatan_perbaikan }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Perbaikan -->
<div class="modal fade" id="modalPerbaikan" tabindex="-1">
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
<div class="modal fade" id="modalAjukanKembali" tabindex="-1">
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
@endsection

@push('styles')
<style>
    th {
        width: 30%;
        background-color: #f8f9fa;
    }
    .card-header.bg-light {
        background-color: #f8f9fa !important;
    }
</style>
@endpush