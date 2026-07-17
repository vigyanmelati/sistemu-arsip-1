{{-- resources/views/arsip-masuk/show.blade.php --}}

@extends('layouts.app')

@section('page-title', 'Detail Arsip Masuk')
@section('page-subtitle', 'Verifikasi Kelengkapan Arsip')

@section('content')
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

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Validasi gagal:</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="row">
    <div class="col-md-8">
        <!-- Tombol Kembali -->
        <div class="mb-3">
            <a href="{{ route('arsip-masuk.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Arsip Masuk
            </a>
        </div>

        <!-- Informasi Arsip -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Informasi Arsip
                </h5>
                <span class="badge bg-light text-dark">
                    <i class="bi bi-clock-history me-1"></i>
                    {{ $arsip->created_at ? $arsip->created_at->format('d/m/Y H:i') : '-' }}
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="35%">Kode Klasifikasi</th>
                                <td>: <strong>{{ $arsip->kodeKlasifikasi->kode ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <th>Judul Arsip</th>
                                <td>: {{ $arsip->uraian_arsip }}</td>
                            </tr>
                            <tr>
                                <th>Sub Bagian Pengaju</th>
                                <td>: {{ $arsip->subBagian->nama_sub_bagian ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Tahun Arsip</th>
                                <td>: {{ $arsip->tahun_arsip }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Arsip</th>
                                <td>: {{ $arsip->tanggal_arsip ? \Carbon\Carbon::parse($arsip->tanggal_arsip)->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jumlah Berkas</th>
                                <td>: {{ $arsip->jumlah_berkas }} {{ $arsip->satuan_arsip }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="35%">Status Arsip</th>
                                <td>: 
                                    @php
                                        $statusColors = [
                                            'AKTIF' => 'success',
                                            'INAKTIF' => 'warning',
                                            'PERMANEN' => 'info',
                                            'MUSNAH' => 'danger',
                                            'HABIS_RETENSI' => 'secondary'
                                        ];
                                        $color = $statusColors[$arsip->status_arsip] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ $arsip->status_arsip }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Aktif Sampai</th>
                                <td>: {{ $arsip->aktif_sampai ? \Carbon\Carbon::parse($arsip->aktif_sampai)->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Inaktif Sampai</th>
                                <td>: {{ $arsip->inaktif_sampai ? \Carbon\Carbon::parse($arsip->inaktif_sampai)->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Keterangan JRA</th>
                                <td>: {{ $arsip->keterangan_jra ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Lokasi Asal</th>
                                <td>: 
                                    @php
                                        $lokasiLabels = [
                                            'RUANG_SUBBAGIAN_UMUM_LOGISTIK' => 'Subbagian Umum & Logistik',
                                            'RUANG_SUBBAGIAN_PARTISIPASI_MASYARAKAT_SDM' => 'Subbagian Parmas & SDM',
                                            'RUANG_SUBBAGIAN_KEUANGAN' => 'Subbagian Keuangan',
                                            'RUANG_SUBBAGIAN_PERENCANAAN_DATA_INFORMASI' => 'Subbagian Perencanaan, Data & Informasi',
                                            'RUANG_SUBBAGIAN_TEKNIS' => 'Subbagian Teknis',
                                            'RUANG_SUBBAGIAN_HUKUM' => 'Subbagian Hukum',
                                        ];
                                        $lokasiLabel = $lokasiLabels[$arsip->lokasi_arsip] ?? $arsip->lokasi_arsip ?? '-';
                                    @endphp
                                    {{ $lokasiLabel }}
                                </td>
                            </tr>
                            <tr>
                                <th>Lokasi Rak & Box</th>
                                <td>: Rak <strong>{{ $arsip->rak ? $arsip->rak->nomor_rak : '-' }}</strong>, 
                                    Box <strong>{{ $arsip->box ? $arsip->box->nomor_box : '-' }}</strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Preview / File Dokumen Arsip -->
                <div class="mt-3 pt-3 border-top">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-file-earmark me-2"></i>File Dokumen Arsip
                    </label>
                   {{-- resources/views/arsip-masuk/show.blade.php --}}

<!-- Preview / File Dokumen Arsip -->
{{-- resources/views/arsip-masuk/show.blade.php --}}

<!-- Preview / File Dokumen Arsip -->
<div class="mt-3 pt-3 border-top">
    <label class="form-label fw-semibold">
        <i class="bi bi-file-earmark me-2"></i>File Dokumen Arsip
    </label>
    @if($arsip->file_dokumen)
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <!-- Tombol Download - PASTIKAN ROUTE INI -->
            <a href="{{ route('arsip-masuk.download-file', $arsip->id) }}" 
               class="btn btn-sm btn-success"
               download>
                <i class="bi bi-download me-1"></i> Download
            </a>
            
            <!-- Tombol Preview -->
            <a href="{{ asset('storage/arsip/' . $arsip->file_dokumen) }}" 
               target="_blank" 
               class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye me-1"></i> Preview
            </a>
            
            <!-- Info file -->
            <small class="text-muted">
                <i class="bi bi-file-earmark me-1"></i>
                {{ $arsip->file_dokumen }}
                @php
                    $paths = [
                        storage_path('app/public/arsip/' . $arsip->file_dokumen),
                        public_path('storage/arsip/' . $arsip->file_dokumen),
                    ];
                    $fileSize = null;
                    foreach ($paths as $path) {
                        if (file_exists($path)) {
                            $fileSize = filesize($path);
                            break;
                        }
                    }
                    if ($fileSize) {
                        $sizeStr = $fileSize > 1048576 ? round($fileSize/1048576, 2) . ' MB' : 
                                  ($fileSize > 1024 ? round($fileSize/1024, 2) . ' KB' : $fileSize . ' B');
                        echo '<span class="text-muted ms-2">(' . $sizeStr . ')</span>';
                    }
                @endphp
            </small>
        </div>
    @else
        <span class="text-muted">
            <i class="bi bi-file-earmark me-1"></i>
            Tidak ada file
        </span>
    @endif
</div>
                </div>
            </div>
        </div>
        
        <!-- File Berita Acara -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    File Berita Acara
                </h5>
            </div>
            <div class="card-body">
                {{-- Jika arsip diajukan kembali setelah perbaikan --}}
@if($arsip->status_pindah == 'DIAJUKAN')

    {{-- Catatan Verifikasi Sebelumnya --}}
    @if($arsip->catatan_verifikasi)
        <div class="alert alert-danger mb-3">
            <h6 class="fw-bold mb-2">
                <i class="bi bi-x-circle-fill me-2"></i>
                Catatan Verifikasi
            </h6>

            <p class="mb-0" style="white-space: pre-line;">
                {{ $arsip->catatan_verifikasi }}
            </p>
        </div>
    @endif

    {{-- Catatan Perbaikan dari Sub Bagian --}}
    @if($arsip->catatan_perbaikan)
        <div class="alert alert-info mb-3">
            <h6 class="fw-bold mb-2">
                <i class="bi bi-pencil-square me-2"></i>
                Catatan Perbaikan
            </h6>

            <p class="mb-0" style="white-space: pre-line;">
                {{ $arsip->catatan_perbaikan }}
            </p>
        </div>
    @endif

@endif
                @if($arsip->beritaAcaraPindah->isNotEmpty())
                    @php $bap = $arsip->beritaAcaraPindah->first(); @endphp
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nomor BAP:</strong> {{ $bap->nomor_bap }}</p>
                                    <p><strong>Tanggal BAP:</strong> {{ $bap->tanggal_bap ? \Carbon\Carbon::parse($bap->tanggal_bap)->format('d-m-Y') : '-' }}</p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <a href="{{ route('arsip-masuk.download-berita-acara', $arsip->id) }}" class="btn btn-primary" target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i> Download Berita Acara
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-excel text-warning" style="font-size: 4rem;"></i>
                        <h5 class="mt-3">File Berita Acara Tidak Ditemukan</h5>
                        <p class="text-muted">Sub Bagian belum mengupload file berita acara</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Panel Verifikasi -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="bi bi-clipboard-check me-2"></i>
                    Verifikasi Arsip
                </h5>
            </div>
            <div class="card-body">
                <!-- TAMPILKAN CATATAN VERIFIKASI -->
                @if($arsip->catatan_verifikasi)
                    <div class="alert alert-{{ 
                        strpos($arsip->catatan_verifikasi, '✅ DISETUJUI') !== false ? 'success' : 
                        (strpos($arsip->catatan_verifikasi, '❌ DITOLAK') !== false ? 'danger' : 
                        (strpos($arsip->catatan_verifikasi, '📦 PINDAH') !== false ? 'info' : 'secondary'))
                    }} mb-3">
                        <h6 class="fw-bold">
                            <i class="bi 
                                @if(strpos($arsip->catatan_verifikasi, '✅ DISETUJUI') !== false) bi-check-circle-fill text-success
                                @elseif(strpos($arsip->catatan_verifikasi, '❌ DITOLAK') !== false) bi-x-circle-fill text-danger
                                @elseif(strpos($arsip->catatan_verifikasi, '📦 PINDAH') !== false) bi-box-seam-fill text-info
                                @endif me-2
                            "></i>
                            Catatan Verifikasi
                        </h6>
                        <p class="mb-0" style="white-space: pre-line;">{{ $arsip->catatan_verifikasi }}</p>
                        {{-- <small class="text-muted d-block mt-2">
                            Oleh: {{ $arsip->verifikator->name ?? 'System' }} | 
                            {{ $arsip->tanggal_diverifikasi ? \Carbon\Carbon::parse($arsip->tanggal_diverifikasi)->format('d/m/Y H:i') : '-' }}
                        </small> --}}
                    </div>
                @endif

                @if($arsip->status_pindah == 'DIAJUKAN')
                {{-- FORM VERIFIKASI DENGAN DROPDOWN --}}
                <form action="{{ route('admin.arsip-masuk.verifikasi', $arsip->id) }}" 
      method="POST"
      id="formVerifikasi" novalidate>
    @csrf
                     
                    
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Info:</strong> Pilih tindakan verifikasi untuk arsip ini.
                    </div>
                    
                    <!-- Dropdown Tindakan -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tindakan Verifikasi <span class="text-danger">*</span></label>
                        <select name="tindakan" id="tindakan" class="form-select" required>
                            <option value="">-- Pilih Tindakan --</option>
                            <option value="setujui">✅ Setujui Arsip</option>
                            <option value="tolak">❌ Tolak Arsip</option>
                        </select>
                    </div>
                    
                    <!-- FORM SETUJUI (muncul jika pilih setujui) - TANPA CATATAN -->
                    <div id="formSetujui" style="display: none;">
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <strong>Setujui Arsip</strong>
                            <p class="mb-0 mt-1">Pilih lokasi tujuan untuk arsip ini.</p>
                        </div>
                        
                        <!-- Pilih Lokasi Tujuan -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lokasi Tujuan <span class="text-danger">*</span></label>
                            <select name="lokasi_tujuan" id="lokasi_tujuan" class="form-select">
                                <option value="">-- Pilih Lokasi --</option>
                                <option value="RECORD_CENTER_PERMANEN">Record Center Permanen</option>
                                <option value="RECORD_CENTER_INAKTIF">Record Center Inaktif</option>
                            </select>
                        </div>
                        
                        <!-- Pilih Rak -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rak Tujuan <span class="text-danger">*</span></label>
                            <select name="rak_id_baru" id="rak_id_baru" class="form-select">
                                <option value="">-- Pilih Lokasi Terlebih Dahulu --</option>
                            </select>
                        </div>
                        
                        <!-- Pilih Box -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Box Tujuan <span class="text-danger">*</span></label>
                            <select name="box_id_baru" id="box_id_baru" class="form-select" >
                                <option value="">-- Pilih Rak Terlebih Dahulu --</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- FORM TOLAK (muncul jika pilih tolak) - HANYA ALASAN -->
                    <div id="formTolak" style="display: none;">
                        <div class="alert alert-danger">
                            <i class="bi bi-x-circle-fill me-2"></i>
                            <strong>Tolak Arsip</strong>
                            <p class="mb-0 mt-1">Arsip akan dikembalikan ke Sub Bagian untuk perbaikan.</p>
                        </div>
                        
                        <!-- Alasan Penolakan -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="alasan" id="alasan" class="form-control" rows="3" required
                                    placeholder="Jelaskan alasan penolakan..."></textarea>
                            <small class="text-muted">Alasan ini akan dikirim ke Sub Bagian</small>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="btnSubmit">
                            <i class="bi bi-check-circle me-2"></i>Proses Verifikasi
                        </button>
                    </div>
                </form>

                @elseif($arsip->status_pindah == 'DITERIMA')
                {{-- FORM PINDAHKAN - DENGAN PILIHAN LOKASI --}}
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Arsip telah diverifikasi.</strong>
                    <p class="mb-0 mt-1">Silakan pilih lokasi dan pindahkan arsip.</p>
                </div>
                
                <form action="{{ route('admin.arsip-masuk.pindahkan', $arsip->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status Arsip <span class="text-danger">*</span></label>
                        <select name="status_arsip_setelah_pindah" class="form-select" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="AKTIF">Aktif</option>
                            <option value="INAKTIF">Inaktif</option>
                            <option value="PERMANEN">Permanen</option>
                            <option value="MUSNAH">Musnah</option>
                        </select>
                    </div>
                    
                    <!-- Pilih Lokasi Tujuan -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lokasi Tujuan <span class="text-danger">*</span></label>
                        <select name="lokasi_tujuan" id="lokasi_tujuan_pindah" class="form-select" >
                            <option value="">-- Pilih Lokasi --</option>
                            <option value="RECORD_CENTER_PERMANEN">Record Center Permanen</option>
                            <option value="RECORD_CENTER_INAKTIF">Record Center Inaktif</option>
                        </select>
                    </div>
                    
                    <!-- Pilih Rak -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rak Tujuan <span class="text-danger">*</span></label>
                        <select name="rak_id_baru" id="rak_id_baru_pindah" class="form-select">
                            <option value="">-- Pilih Lokasi Terlebih Dahulu --</option>
                        </select>
                    </div>
                    
                    <!-- Pilih Box -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Box Tujuan <span class="text-danger">*</span></label>
                        <select name="box_id_baru" id="box_id_baru_pindah" class="form-select" >
                            <option value="">-- Pilih Rak Terlebih Dahulu --</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan Pemindahan</label>
                        <textarea name="catatan_pemindahan" class="form-control" rows="2" 
                                placeholder="Catatan proses pemindahan..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-right-circle me-2"></i>Pindahkan Arsip
                    </button>
                </form>

                @elseif($arsip->status_pindah == 'DIPINDAHKAN')
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Arsip sudah dipindahkan.</strong>
                    <p class="mb-0 mt-1">Status: {{ $arsip->status_arsip }}</p>
                    @if($arsip->rak)
                        <p class="small mt-1">Lokasi: Rak {{ $arsip->rak->nomor_rak }}, Box {{ $arsip->box->nomor_box }}</p>
                    @endif
                </div>
                
                @elseif($arsip->status_pindah == 'DITOLAK')
<div class="alert alert-danger">
    <i class="bi bi-x-circle-fill me-2"></i>
    <strong>Arsip ditolak.</strong>
    @if($arsip->catatan_verifikasi)
        <p class="mb-0 mt-1">{{ $arsip->catatan_verifikasi }}</p>
    @endif
</div>
@else
<div class="alert alert-secondary">
    <i class="bi bi-question-circle-fill me-2"></i>
    <strong>Status arsip: {{ $arsip->status_pindah ?? 'Tidak diketahui' }}</strong>
    <p class="mb-0 mt-1">Arsip berada pada status yang belum memiliki tampilan verifikasi khusus.</p>
</div>
@endif
            </div>
        </div>
        
        <!-- Timeline Verifikasi -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Timeline Verifikasi
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Diajukan oleh Sub Bagian</h6>
                            <p class="text-muted small mb-0">{{ $arsip->created_at ? $arsip->created_at->format('d/m/Y H:i') : '-' }}</p>
                            <small>Lokasi: {{ $arsip->rak ? 'Rak '.$arsip->rak->nomor_rak : '-' }}, {{ $arsip->box ? 'Box '.$arsip->box->nomor_box : '-' }}</small>
                        </div>
                    </div>
                    
                    @if($arsip->tanggal_diverifikasi)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-{{ 
                            $arsip->status_pindah == 'DITOLAK' ? 'danger' : 
                            ($arsip->status_pindah == 'DIPINDAHKAN' ? 'success' : 'warning') 
                        }}"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">
                                {{ $arsip->status_pindah == 'DITOLAK' ? 'Ditolak' : 'Diverifikasi' }}
                            </h6>
                            <p class="text-muted small mb-0">
                                {{ $arsip->tanggal_diverifikasi ? \Carbon\Carbon::parse($arsip->tanggal_diverifikasi)->format('d/m/Y H:i') : '-' }}
                            </p>
                            @if($arsip->catatan_verifikasi)
                                <p class="small mt-1" style="white-space: pre-line;">{{ $arsip->catatan_verifikasi }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    @if($arsip->status_pindah == 'DIPINDAHKAN')
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Dipindahkan ke Unit Kearsipan</h6>
                            <p class="text-muted small mb-0">
                                {{ $arsip->tanggal_dipindahkan ? \Carbon\Carbon::parse($arsip->tanggal_dipindahkan)->format('d/m/Y H:i') : '-' }}
                            </p>
                            @if($arsip->rak)
                                <p class="small">Lokasi Baru: Rak {{ $arsip->rak->nomor_rak }}, Box {{ $arsip->box->nomor_box }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    
    .timeline-marker {
        position: absolute;
        left: -30px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        top: 4px;
    }
    
    .timeline-content {
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .timeline-content:last-child {
        border-bottom: none;
    }

    .alert {
        border-left: 4px solid;
    }
    .alert-success { border-left-color: #28a745; }
    .alert-danger { border-left-color: #dc3545; }
    .alert-warning { border-left-color: #ffc107; }
    .alert-info { border-left-color: #17a2b8; }

    .table-borderless td {
        padding: 4px 0;
    }
    .table-borderless th {
        padding: 4px 0;
        font-weight: 600;
        color: #495057;
    }
    .card-header {
        border-radius: 8px 8px 0 0;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tindakan = document.getElementById('tindakan');
    const formSetujui = document.getElementById('formSetujui');
    const formTolak = document.getElementById('formTolak');
    const alasan = document.getElementById('alasan');
    const btnSubmit = document.getElementById('btnSubmit');
    const formVerifikasi = document.getElementById('formVerifikasi');
    
    // Show/hide form berdasarkan pilihan
    function toggleForms() {
        const selected = tindakan.value;
        
        // Sembunyikan semua
        formSetujui.style.display = 'none';
        formTolak.style.display = 'none';
        
        // Tampilkan yang sesuai
        if (selected === 'setujui') {
            formSetujui.style.display = 'block';
            btnSubmit.innerHTML = '<i class="bi bi-check-circle me-2"></i>Setujui Arsip';
            btnSubmit.className = 'btn btn-success w-100';
        } else if (selected === 'tolak') {
            formTolak.style.display = 'block';
            btnSubmit.innerHTML = '<i class="bi bi-x-circle me-2"></i>Tolak Arsip';
            btnSubmit.className = 'btn btn-danger w-100';
        } else {
            btnSubmit.innerHTML = '<i class="bi bi-check-circle me-2"></i>Proses Verifikasi';
            btnSubmit.className = 'btn btn-primary w-100';
        }
    }
    
    if (tindakan) {
        tindakan.addEventListener('change', toggleForms);
    }
    
    // Validasi form sebelum submit
    if (formVerifikasi) {
        formVerifikasi.addEventListener('submit', function(e) {
            const selected = tindakan.value;
            
            console.log('Tindakan dipilih:', selected); // Debug
            
            if (!selected) {
                e.preventDefault();
                alert('Silakan pilih tindakan verifikasi terlebih dahulu.');
                return;
            }
            
            if (selected === 'tolak') {
                const alasanValue = alasan.value.trim();
                if (!alasanValue) {
                    e.preventDefault();
                    alert('Silakan isi alasan penolakan.');
                    alasan.focus();
                    return;
                }
            }
            
            if (selected === 'setujui') {
                const lokasi = document.getElementById('lokasi_tujuan');
                const rak = document.getElementById('rak_id_baru');
                const box = document.getElementById('box_id_baru');
                
                console.log('Lokasi:', lokasi ? lokasi.value : 'null');
                console.log('Rak:', rak ? rak.value : 'null');
                console.log('Box:', box ? box.value : 'null');
                
                if (!lokasi || !lokasi.value) {
                    e.preventDefault();
                    alert('Silakan pilih lokasi tujuan.');
                    return;
                }
                if (!rak || !rak.value) {
                    e.preventDefault();
                    alert('Silakan pilih rak tujuan.');
                    return;
                }
                if (!box || !box.value) {
                    e.preventDefault();
                    alert('Silakan pilih box tujuan.');
                    return;
                }
            }
            
            // Konfirmasi
            const tindakanLabel = selected === 'setujui' ? 'SETUJUI' : 'TOLAK';
            if (!confirm('Yakin akan ' + tindakanLabel + ' arsip ini?')) {
                e.preventDefault();
            }
        });
    }
    
    // Filter Rak & Box untuk FORM SETUJUI
    const lokasiTujuan = document.getElementById('lokasi_tujuan');
    const rakSelect = document.getElementById('rak_id_baru');
    const boxSelect = document.getElementById('box_id_baru');
    
    // Filter Rak & Box untuk FORM PINDAHKAN
    const lokasiTujuanPindah = document.getElementById('lokasi_tujuan_pindah');
    const rakSelectPindah = document.getElementById('rak_id_baru_pindah');
    const boxSelectPindah = document.getElementById('box_id_baru_pindah');
    
    const rakData = @json($rakOptions ?? []);
    const boxData = @json($boxOptions ?? []);
    
    console.log('Rak Data:', rakData);
    console.log('Box Data:', boxData);
    
    function filterRak(lokasi, rakElem, boxElem) {
        if (!lokasi || !rakElem) return;
        
        const selectedLokasi = lokasi.value;
        rakElem.innerHTML = '<option value="">-- Pilih Rak --</option>';
        if (boxElem) boxElem.innerHTML = '<option value="">-- Pilih Rak Terlebih Dahulu --</option>';
        
        if (!selectedLokasi) {
            rakElem.innerHTML = '<option value="">-- Pilih Lokasi Terlebih Dahulu --</option>';
            return;
        }
        
        const filteredRak = rakData.filter(function(rak) {
            return rak.lokasi_arsip === selectedLokasi;
        });
        
        if (filteredRak.length === 0) {
            rakElem.innerHTML = '<option value="">-- Tidak ada rak di lokasi ini --</option>';
        } else {
            filteredRak.forEach(function(rak) {
                const option = document.createElement('option');
                option.value = rak.id;
                option.textContent = rak.nomor_rak;
                rakElem.appendChild(option);
            });
        }
    }
    
    function filterBox(rakElem, boxElem) {
        if (!rakElem || !boxElem) return;
        
        const selectedRakId = rakElem.value;
        boxElem.innerHTML = '<option value="">-- Pilih Box --</option>';
        
        if (!selectedRakId) {
            boxElem.innerHTML = '<option value="">-- Pilih Rak Terlebih Dahulu --</option>';
            return;
        }
        
        const filteredBox = boxData.filter(function(box) {
            return box.rak_id == selectedRakId;
        });
        
        if (filteredBox.length === 0) {
            boxElem.innerHTML = '<option value="">-- Tidak ada box di rak ini --</option>';
        } else {
            filteredBox.forEach(function(box) {
                const option = document.createElement('option');
                option.value = box.id;
                option.textContent = box.nomor_box;
                boxElem.appendChild(option);
            });
        }
    }
    
    // Event listeners untuk FORM SETUJUI
    if (lokasiTujuan) {
        lokasiTujuan.addEventListener('change', function() {
            filterRak(lokasiTujuan, rakSelect, boxSelect);
        });
        // Trigger pertama kali jika sudah ada value
        if (lokasiTujuan.value) {
            filterRak(lokasiTujuan, rakSelect, boxSelect);
        }
    }
    if (rakSelect) {
        rakSelect.addEventListener('change', function() {
            filterBox(rakSelect, boxSelect);
        });
    }
    
    // Event listeners untuk FORM PINDAHKAN
    if (lokasiTujuanPindah) {
        lokasiTujuanPindah.addEventListener('change', function() {
            filterRak(lokasiTujuanPindah, rakSelectPindah, boxSelectPindah);
        });
        if (lokasiTujuanPindah.value) {
            filterRak(lokasiTujuanPindah, rakSelectPindah, boxSelectPindah);
        }
    }
    if (rakSelectPindah) {
        rakSelectPindah.addEventListener('change', function() {
            filterBox(rakSelectPindah, boxSelectPindah);
        });
    }
});
</script>
@endsection