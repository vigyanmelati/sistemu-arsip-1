@extends('layouts.app')

@section('title', 'Detail Surat Masuk')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb (opsional) -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('subbagian.surat-masuk.index') }}">Surat Masuk</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail #{{ $surat->nomor_agenda }}</li>
        </ol>
    </nav>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h4 class="mb-0 text-primary">
                <i class="fas fa-envelope-open-text me-2"></i> Detail Surat Masuk
            </h4>
            <!-- <div>
                <a href="{{ route('surat-masuk.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                <a href="{{ route('surat-masuk.edit', $surat->id) }}" class="btn btn-warning btn-sm text-white">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            </div> -->
        </div>

        <div class="card-body p-4">
            <!-- Informasi Ringkas -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="d-flex align-items-center bg-light p-3 rounded-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-hashtag text-primary fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Nomor Agenda</small>
                            <strong class="fs-5">{{ $surat->nomor_agenda }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center bg-light p-3 rounded-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-calendar-alt text-success fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Tanggal Dokumen</small>
                            <strong class="fs-5">{{ \Carbon\Carbon::parse($surat->tanggal_dokumen)->translatedFormat('d F Y') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail dalam Grid Dua Kolom -->
            <div class="row g-4">
                <!-- Kolom Kiri -->
                <div class="col-lg-6">
                    <div class="card h-100 border-0 bg-light-subtle">
                        <div class="card-body">
                            <h6 class="card-title text-uppercase text-muted small mb-3">
                                <i class="fas fa-info-circle me-1"></i> Informasi Surat
                            </h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-muted">Nomor Surat</dt>
                                <dd class="col-sm-8">{{ $surat->nomor_dokumen }}</dd>

                                <dt class="col-sm-4 text-muted">Perihal</dt>
                                <dd class="col-sm-8">{{ $surat->perihal }}</dd>

                                <dt class="col-sm-4 text-muted">Kepada</dt>
                                <dd class="col-sm-8">{{ $surat->kepada }}</dd>

                                <dt class="col-sm-4 text-muted">Asal Dokumen</dt>
                                <dd class="col-sm-8">{{ $surat->instansi_satker }}</dd>

                                <dt class="col-sm-4 text-muted">Tujuan Disposisi</dt>
                                <dd class="col-sm-8">{{ $surat->tujuanDisposisis->pluck('nama_tujuan')->join(', ') ?: '-' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="col-lg-6">
                    <div class="card h-100 border-0 bg-light-subtle">
                        <div class="card-body">
                            <h6 class="card-title text-uppercase text-muted small mb-3">
                                <i class="fas fa-paperclip me-1"></i> Lampiran & Catatan
                            </h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-muted">Tanggal Penyelesaian</dt>
                                <dd class="col-sm-8">{{ \Carbon\Carbon::parse($surat->tanggal_penyelesaian)->translatedFormat('d F Y') }}</dd>

                                <dt class="col-sm-4 text-muted">Keterangan</dt>
                                <dd class="col-sm-8">{{ $surat->catatan ?: '-' }}</dd>

                                <dt class="col-sm-4 text-muted">Lampiran</dt>
                                <dd class="col-sm-8">
                                    @if($surat->file_input)
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ asset('storage/surat_masuk/'.$surat->file_input) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i> Lihat
                                            </a>
                                            <a href="{{ asset('storage/surat_masuk/'.$surat->file_input) }}" download class="btn btn-outline-success btn-sm">
                                                <i class="fas fa-download me-1"></i> Download
                                            </a>
                                            <!-- Jika file berupa gambar, tampilkan preview -->
                                            @php
                                                $extension = pathinfo($surat->file_input, PATHINFO_EXTENSION);
                                            @endphp
                                            @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']))
                                                <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#previewModal">
                                                    <i class="fas fa-image me-1"></i> Preview
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Tidak ada lampiran</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jika ada file gambar, modal preview -->
            @if($surat->file_input && in_array(strtolower(pathinfo($surat->file_input, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','bmp','webp']))
            <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Preview Lampiran</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="{{ asset('storage/surat_masuk/'.$surat->file_input) }}" class="img-fluid" alt="Lampiran Surat">
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>

        <div class="card-footer bg-white border-0 d-flex justify-content-end gap-2 py-3">
            <a href="{{ route('subbagian.surat-masuk.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
</div>
@endsection
