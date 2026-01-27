@extends('layouts.app')

@section('page-title', 'Edit Arsip')
@section('page-subtitle', 'Form Edit Arsip Digital')

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Form Edit Arsip</h6>
    </div>
    <div class="card-body">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <form action="{{ route('arsip.update', $arsip->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- BAGIAN DATA DASAR (WAJIB) -->
            <h6 class="mb-3 text-primary">Data Dasar Arsip (Wajib)</h6>
            <div class="row">
                <!-- Kode Klasifikasi (WAJIB) -->
                <div class="col-md-6 mb-3">
                    <label for="kode_klasifikasi_id" class="form-label">Kode Klasifikasi <span class="text-danger">*</span></label>
                    <select class="form-control @error('kode_klasifikasi_id') is-invalid @enderror" 
                            id="kode_klasifikasi_id" name="kode_klasifikasi_id" required>
                        <option value="">Pilih Kode Klasifikasi</option>
                        @foreach($kodeKlasifikasiOptions as $kode)
                            <option value="{{ $kode->id }}" {{ old('kode_klasifikasi_id', $arsip->kode_klasifikasi_id) == $kode->id ? 'selected' : '' }}>
                                {{ $kode->kode }} - {{ $kode->uraian }}
                            </option>
                        @endforeach
                    </select>
                    @error('kode_klasifikasi_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Sub Bagian (WAJIB) -->
                <div class="col-md-6 mb-3">
                    <label for="sub_bagian_id" class="form-label">Sub Bagian <span class="text-danger">*</span></label>
                    <select class="form-control @error('sub_bagian_id') is-invalid @enderror" 
                            id="sub_bagian_id" name="sub_bagian_id" required>
                        <option value="">Pilih Sub Bagian</option>
                        @foreach($subBagianOptions as $subBagian)
                            <option value="{{ $subBagian->id }}" {{ old('sub_bagian_id', $arsip->sub_bagian_id) == $subBagian->id ? 'selected' : '' }}>
                                {{ $subBagian->nama_sub_bagian }}
                            </option>
                        @endforeach
                    </select>
                    @error('sub_bagian_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Judul/Uraian Arsip (WAJIB) -->
                <div class="col-md-12 mb-3">
                    <label for="uraian_arsip" class="form-label">Uraian/Judul Arsip <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('uraian_arsip') is-invalid @enderror" 
                        id="uraian_arsip" name="uraian_arsip" 
                        value="{{ old('uraian_arsip', $arsip->uraian_arsip) }}" required>
                    @error('uraian_arsip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Tahun Arsip (WAJIB) -->
                <div class="col-md-3 mb-3">
                    <label for="tahun_arsip" class="form-label">Tahun Arsip <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('tahun_arsip') is-invalid @enderror" 
                        id="tahun_arsip" name="tahun_arsip" 
                        value="{{ old('tahun_arsip', $arsip->tahun_arsip) }}" 
                        min="2000" max="{{ date('Y') + 1 }}" required>
                    @error('tahun_arsip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Tanggal Arsip (WAJIB) -->
                <div class="col-md-3 mb-3">
                    <label for="tanggal_arsip" class="form-label">Tanggal Arsip <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('tanggal_arsip') is-invalid @enderror" 
                        id="tanggal_arsip" name="tanggal_arsip" 
                        value="{{ old('tanggal_arsip', $arsip->tanggal_arsip) }}" required>
                    @error('tanggal_arsip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Jumlah Berkas (WAJIB) -->
                <div class="col-md-3 mb-3">
                    <label for="jumlah_berkas" class="form-label">Jumlah Berkas <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('jumlah_berkas') is-invalid @enderror" 
                        id="jumlah_berkas" name="jumlah_berkas" 
                        value="{{ old('jumlah_berkas', $arsip->jumlah_berkas) }}" min="1" required>
                    @error('jumlah_berkas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Satuan Arsip (WAJIB) -->
                <div class="col-md-3 mb-3">
                    <label for="satuan_arsip" class="form-label">Satuan Arsip <span class="text-danger">*</span></label>
                    <select class="form-control @error('satuan_arsip') is-invalid @enderror" 
                            id="satuan_arsip" name="satuan_arsip" required>
                        <option value="">Pilih Satuan</option>
                        <option value="BENDEL" {{ old('satuan_arsip', $arsip->satuan_arsip) == 'BENDEL' ? 'selected' : '' }}>BENDEL</option>
                        <option value="LEMBAR" {{ old('satuan_arsip', $arsip->satuan_arsip) == 'LEMBAR' ? 'selected' : '' }}>LEMBAR</option>
                    </select>
                    @error('satuan_arsip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Kondisi Fisik -->
                <div class="col-md-6 mb-3">
                    <label for="keterangan" class="form-label">Kondisi Fisik</label>
                    <select class="form-control @error('keterangan') is-invalid @enderror" 
                            id="keterangan" name="keterangan">
                        <option value="">Pilih Kondisi</option>
                        <option value="BAIK" {{ old('keterangan', $arsip->keterangan) == 'BAIK' ? 'selected' : '' }}>Baik</option>
                        <option value="RUSAK" {{ old('keterangan', $arsip->keterangan) == 'RUSAK' ? 'selected' : '' }}>Rusak</option>
                        <option value="HILANG" {{ old('keterangan', $arsip->keterangan) == 'HILANG' ? 'selected' : '' }}>Hilang</option>
                    </select>
                    @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                 <!-- Tingkat Perkembangan -->
                <div class="col-md-6 mb-3">
                    <label for="tingkat_perkembangan" class="form-label">Tingkat Perkembangan</label>
                    <select class="form-control @error('tingkat_perkembangan') is-invalid @enderror" 
                            id="tingkat_perkembangan" name="tingkat_perkembangan">
                        <option value="">Pilih Tingkat</option>
                        <option value="ASLI" {{ old('tingkat_perkembangan', $arsip->tingkat_perkembangan) == 'ASLI' ? 'selected' : '' }}>ASLI</option>
                        <option value="COPY" {{ old('tingkat_perkembangan', $arsip->tingkat_perkembangan) == 'COPY' ? 'selected' : '' }}>COPY</option>
                        <option value="SALINAN" {{ old('tingkat_perkembangan', $arsip->tingkat_perkembangan) == 'SALINAN' ? 'selected' : '' }}>SALINAN</option>
                    </select>
                    @error('tingkat_perkembangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <hr class="my-4">
            
            <!-- MODE PENGISIAN RETENSI -->
            <h6 class="mb-3 text-primary">Mode Pengisian Masa Retensi Arsip</h6>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_isi_keterangan" 
                                       id="mode_tidak_keterangan" value="0" 
                                       {{ old('is_isi_keterangan', $arsip->is_isi_keterangan) == 0 ? 'checked' : '' }}>
                                <label class="form-check-label" for="mode_tidak_keterangan">
                                    <strong>Mode 1: Tidak Isi Keterangan (Otomatis)</strong>
                                </label>
                                <small class="d-block text-muted">
                                    Input angka tahun saja, sistem hitung otomatis
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_isi_keterangan" 
                                       id="mode_isi_keterangan" value="1"
                                       {{ old('is_isi_keterangan', $arsip->is_isi_keterangan) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="mode_isi_keterangan">
                                    <strong>Mode 2: Isi Keterangan (Deskriptif)</strong>
                                </label>
                                <small class="d-block text-muted">
                                    Input deskripsi lengkap, hitung berdasarkan tanggal referensi
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- MODE 1: TIDAK ISI KETERANGAN (OTOMATIS) -->
            <div id="mode_tidak_keterangan_container" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">Mode Otomatis</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Aktif Tahun (angka) -->
                            <div class="col-md-4 mb-3">
                                <label for="aktif_tahun" class="form-label">Aktif (Tahun)</label>
                                <input type="number" class="form-control @error('aktif_tahun') is-invalid @enderror" 
                                       id="aktif_tahun" name="aktif_tahun" 
                                       value="{{ old('aktif_tahun', $arsip->aktif_tahun) }}" 
                                       placeholder="Contoh: 1" min="1">
                                <small class="text-muted">Angka tahun masa aktif</small>
                                @error('aktif_tahun')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Inaktif Tahun (angka) -->
                            <div class="col-md-4 mb-3">
                                <label for="inaktif_tahun" class="form-label">Inaktif (Tahun)</label>
                                <input type="number" class="form-control @error('inaktif_tahun') is-invalid @enderror" 
                                       id="inaktif_tahun" name="inaktif_tahun" 
                                       value="{{ old('inaktif_tahun', $arsip->inaktif_tahun) }}" 
                                       placeholder="Contoh: 5" min="1">
                                <small class="text-muted">Angka tahun masa inaktif</small>
                                @error('inaktif_tahun')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Keterangan JRA -->
                            <div class="col-md-4 mb-3">
                                <label for="keterangan_jra_otomatis" class="form-label">Keterangan JRA</label>
                                <select class="form-control @error('keterangan_jra') is-invalid @enderror" 
                                        id="keterangan_jra_otomatis" name="keterangan_jra">
                                    <option value="">Pilih Keterangan</option>
                                    <option value="MUSNAH" {{ old('keterangan_jra', $arsip->keterangan_jra) == 'MUSNAH' ? 'selected' : '' }}>MUSNAH</option>
                                    <option value="PERMANEN" {{ old('keterangan_jra', $arsip->keterangan_jra) == 'PERMANEN' ? 'selected' : '' }}>PERMANEN</option>
                                </select>
                                @error('keterangan_jra')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- HASIL PERHITUNGAN OTOMATIS -->
                        <div class="row mt-3">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Aktif Sampai (Otomatis)</label>
                                <div class="form-control" style="background-color: #e9ecef;">
                                    <span id="aktif_sampai_otomatis_preview">
                                        @if($arsip->aktif_sampai)
                                            {{ \Carbon\Carbon::parse($arsip->aktif_sampai)->isoFormat('D MMMM YYYY') }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                </div>
                                <input type="hidden" name="aktif_sampai" id="aktif_sampai_otomatis" value="{{ old('aktif_sampai', $arsip->aktif_sampai) }}">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Inaktif Sampai (Otomatis)</label>
                                <div class="form-control" style="background-color: #e9ecef;">
                                    <span id="inaktif_sampai_otomatis_preview">
                                        @if($arsip->inaktif_sampai)
                                            {{ \Carbon\Carbon::parse($arsip->inaktif_sampai)->isoFormat('D MMMM YYYY') }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                </div>
                                <input type="hidden" name="inaktif_sampai" id="inaktif_sampai_otomatis" value="{{ old('inaktif_sampai', $arsip->inaktif_sampai) }}">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status Arsip (Otomatis)</label>
                                <div class="form-control" style="background-color: #e9ecef;">
                                    <span id="status_arsip_otomatis_preview">{{ $arsip->status_arsip }}</span>
                                </div>
                                <input type="hidden" name="status_arsip" id="status_arsip_otomatis" value="{{ old('status_arsip', $arsip->status_arsip) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- MODE 2: ISI KETERANGAN (DESKRIPTIF) -->
            <div id="mode_isi_keterangan_container" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Mode Deskriptif</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Aktif Keterangan -->
                            <div class="col-md-6 mb-3">
                                <label for="aktif_keterangan" class="form-label">Masa Aktif (Deskripsi)</label>
                                <input type="text" class="form-control @error('aktif_keterangan') is-invalid @enderror" 
                                       id="aktif_keterangan" name="aktif_keterangan" 
                                       value="{{ old('aktif_keterangan', $arsip->aktif_keterangan) }}"
                                       placeholder="Contoh: 1 Tahun Setelah Barang Tidak Dikuasai">
                                @error('aktif_keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Inaktif Keterangan -->
                            <div class="col-md-6 mb-3">
                                <label for="inaktif_keterangan" class="form-label">Masa Inaktif (Deskripsi)</label>
                                <input type="text" class="form-control @error('inaktif_keterangan') is-invalid @enderror" 
                                       id="inaktif_keterangan" name="inaktif_keterangan" 
                                       value="{{ old('inaktif_keterangan', $arsip->inaktif_keterangan) }}"
                                       placeholder="Contoh: 5 Tahun Setelah UU Pertanggung-jawaban APBN...">
                                @error('inaktif_keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Tanggal Referensi -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_referensi" class="form-label">Tanggal Referensi</label>
                                <input type="date" class="form-control @error('tanggal_referensi') is-invalid @enderror" 
                                       id="tanggal_referensi" name="tanggal_referensi" 
                                       value="{{ old('tanggal_referensi', $arsip->tanggal_referensi) }}">
                                <small class="text-muted">
                                    Tanggal barang tidak digunakan / APBN disahkan / acuan lainnya
                                </small>
                                @error('tanggal_referensi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Keterangan JRA -->
                            <div class="col-md-6 mb-3">
                                <label for="keterangan_jra_deskriptif" class="form-label">Keterangan JRA</label>
                                <select class="form-control @error('keterangan_jra') is-invalid @enderror" 
                                        id="keterangan_jra_deskriptif" name="keterangan_jra">
                                    <option value="">Pilih Keterangan</option>
                                    <option value="MUSNAH" {{ old('keterangan_jra', $arsip->keterangan_jra) == 'MUSNAH' ? 'selected' : '' }}>MUSNAH</option>
                                    <option value="PERMANEN" {{ old('keterangan_jra', $arsip->keterangan_jra) == 'PERMANEN' ? 'selected' : '' }}>PERMANEN</option>
                                </select>
                                @error('keterangan_jra')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- HASIL PERHITUNGAN DESKRIPTIF -->
                        <div class="row mt-3">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Aktif Sampai</label>
                                <div class="form-control" style="background-color: #e9ecef;">
                                    <span id="aktif_sampai_deskriptif_preview">
                                        @if($arsip->aktif_sampai)
                                            {{ \Carbon\Carbon::parse($arsip->aktif_sampai)->isoFormat('D MMMM YYYY') }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                </div>
                                <input type="hidden" name="aktif_sampai" id="aktif_sampai_deskriptif" value="{{ old('aktif_sampai', $arsip->aktif_sampai) }}">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Inaktif Sampai</label>
                                <div class="form-control" style="background-color: #e9ecef;">
                                    <span id="inaktif_sampai_deskriptif_preview">
                                        @if($arsip->inaktif_sampai)
                                            {{ \Carbon\Carbon::parse($arsip->inaktif_sampai)->isoFormat('D MMMM YYYY') }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                </div>
                                <input type="hidden" name="inaktif_sampai" id="inaktif_sampai_deskriptif" value="{{ old('inaktif_sampai', $arsip->inaktif_sampai) }}">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status Arsip</label>
                                <div id="status_arsip_deskriptif_container">
                                    <!-- Jika tanggal referensi kosong, tampilkan dropdown -->
                                    <select class="form-control" id="status_arsip_deskriptif_select" name="status_arsip" style="display: none;">
                                        <option value="AKTIF" {{ old('status_arsip', $arsip->status_arsip) == 'AKTIF' ? 'selected' : '' }}>Aktif</option>
                                        <option value="INAKTIF" {{ old('status_arsip', $arsip->status_arsip) == 'INAKTIF' ? 'selected' : '' }}>Inaktif</option>
                                        <option value="USUL_MUSNAH" {{ old('status_arsip', $arsip->status_arsip) == 'USUL_MUSNAH' ? 'selected' : '' }}>Usul Musnah</option>
                                        <option value="MUSNAH" {{ old('status_arsip', $arsip->status_arsip) == 'MUSNAH' ? 'selected' : '' }}>Musnah</option>
                                        <option value="PERMANEN" {{ old('status_arsip', $arsip->status_arsip) == 'PERMANEN' ? 'selected' : '' }}>Permanen</option>
                                    </select>
                                    <!-- Jika tanggal referensi diisi, tampilkan hasil otomatis -->
                                    <div id="status_arsip_deskriptif_preview_container">
                                        <div class="form-control" style="background-color: #e9ecef;">
                                            <span id="status_arsip_deskriptif_preview">{{ $arsip->status_arsip }}</span>
                                        </div>
                                        <input type="hidden" name="status_arsip" id="status_arsip_deskriptif" value="{{ old('status_arsip', $arsip->status_arsip) }}">
                                    </div>
                                </div>
                                <small class="text-muted">
                                    Pilih manual jika tanggal referensi belum diisi
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <!-- INFORMASI TAMBAHAN (OPTIONAL) -->
            <h6 class="mb-3 text-primary">Informasi Tambahan (Opsional)</h6>
            
            <div class="row">
                <!-- Nomor Rak -->
                <div class="col-md-3 mb-3">
                    <label for="nomor_rak" class="form-label">Nomor Rak</label>
                    <input type="text" class="form-control @error('nomor_rak') is-invalid @enderror" 
                           id="nomor_rak" name="nomor_rak" 
                           value="{{ old('nomor_rak', $arsip->nomor_rak) }}" 
                           placeholder="Misal: 1">
                    @error('nomor_rak')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Nomor Box -->
                <div class="col-md-3 mb-3">
                    <label for="nomor_box" class="form-label">Nomor Box</label>
                    <input type="text" class="form-control @error('nomor_box') is-invalid @enderror" 
                           id="nomor_box" name="nomor_box" 
                           value="{{ old('nomor_box', $arsip->nomor_box) }}" 
                           placeholder="Misal: 1">
                    @error('nomor_box')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- No Sampul -->
                <div class="col-md-3 mb-3">
                    <label for="nomor_sampul" class="form-label">Nomor Sampul</label>
                    <input type="text" class="form-control @error('nomor_sampul') is-invalid @enderror" 
                        id="nomor_sampul" name="nomor_sampul" 
                        value="{{ old('nomor_sampul', $arsip->nomor_sampul) }}" 
                        placeholder="Misal: 1">
                    @error('nomor_sampul')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Tanggal Masuk -->
                <div class="col-md-3 mb-3">
                    <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                    <input type="date" class="form-control @error('tanggal_masuk') is-invalid @enderror" 
                           id="tanggal_masuk" name="tanggal_masuk" 
                           value="{{ old('tanggal_masuk', $arsip->tanggal_masuk) }}">
                    <small class="text-muted">Tanggal arsip dimasukkan ke sistem</small>
                    @error('tanggal_masuk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <!-- FILE UPLOAD -->
                <div class="col-md-6 mb-3">
                    <label for="file_dokumen" class="form-label">File Dokumen</label>
                    @if($arsip->file_dokumen)
                        <div class="mb-2">
                            <span class="badge bg-info">File saat ini:</span>
                            <a href="{{ Storage::url('arsip/' . $arsip->file_dokumen) }}" target="_blank" class="text-decoration-none">
                                {{ $arsip->file_dokumen }}
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusFile()">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                            <input type="hidden" name="hapus_file" id="hapus_file" value="0">
                        </div>
                    @endif
                    <input type="file" class="form-control @error('file_dokumen') is-invalid @enderror" 
                           id="file_dokumen" name="file_dokumen" 
                           accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">
                        Format: PDF, JPG, JPEG, PNG (Maks: 2MB) - Kosongkan jika tidak ingin mengubah
                    </small>
                    @error('file_dokumen')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Hidden input untuk status arsip final -->
            <input type="hidden" name="status_arsip" id="status_arsip_final" value="{{ old('status_arsip', $arsip->status_arsip) }}">
            
            <!-- TOMBOL SIMPAN -->
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('arsip.show', $arsip->id) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Detail
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Arsip
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. TOGGLE MODE RETENSI
     const modeTidakKeterangan = document.getElementById('mode_tidak_keterangan');
    const modeIsiKeterangan = document.getElementById('mode_isi_keterangan');
    const containerTidakKeterangan = document.getElementById('mode_tidak_keterangan_container');
    const containerIsiKeterangan = document.getElementById('mode_isi_keterangan_container');

    function toggleMode() {
        if (modeTidakKeterangan.checked) {
            containerTidakKeterangan.style.display = 'block';
            containerIsiKeterangan.style.display = 'none';
        } else if (modeIsiKeterangan.checked) {
            containerTidakKeterangan.style.display = 'none';
            containerIsiKeterangan.style.display = 'block';
        }
    }

    // Event listener
    modeTidakKeterangan.addEventListener('change', toggleMode);
    modeIsiKeterangan.addEventListener('change', toggleMode);

    // INITIAL LOAD — PAKAI STATE RADIO DARI BLADE
    toggleMode();


    // 2. KALKULASI MODE 1: OTOMATIS
    const tanggalArsip = document.getElementById('tanggal_arsip');
    const aktifTahun = document.getElementById('aktif_tahun');
    const inaktifTahun = document.getElementById('inaktif_tahun');
    const keteranganJraOtomatis = document.getElementById('keterangan_jra_otomatis');
    
    // Preview elements untuk mode 1
    const aktifSampaiOtomatisPreview = document.getElementById('aktif_sampai_otomatis_preview');
    const inaktifSampaiOtomatisPreview = document.getElementById('inaktif_sampai_otomatis_preview');
    const statusArsipOtomatisPreview = document.getElementById('status_arsip_otomatis_preview');
    const statusArsipOtomatisHidden = document.getElementById('status_arsip_otomatis');
    
    function hitungModeOtomatis() {
        console.log('hitungModeOtomatis dipanggil');
        
        if (!tanggalArsip || !aktifTahun) {
            console.log('Tanggal arsip atau aktif tahun tidak ditemukan');
            return;
        }
        
        if (!tanggalArsip.value || !aktifTahun.value) {
            console.log('Tanggal arsip atau aktif tahun kosong');
            resetPreviewOtomatis();
            return;
        }
        
        const tanggal = new Date(tanggalArsip.value);
        const sekarang = new Date();
        
        // Hitung aktif sampai
        const aktifSampai = new Date(tanggal);
        aktifSampai.setFullYear(tanggal.getFullYear() + parseInt(aktifTahun.value));
        
        // Format tanggal untuk display
        const formatTanggal = (date) => {
            if (!date) return '-';
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });
        };
        
        // Update preview aktif sampai
        aktifSampaiOtomatisPreview.textContent = formatTanggal(aktifSampai);
        document.getElementById('aktif_sampai_otomatis').value = aktifSampai.toISOString().split('T')[0];
        
        // Hitung inaktif sampai
        let inaktifSampai = null;
        if (inaktifTahun.value) {
            inaktifSampai = new Date(aktifSampai);
            inaktifSampai.setFullYear(aktifSampai.getFullYear() + parseInt(inaktifTahun.value));
            
            inaktifSampaiOtomatisPreview.textContent = formatTanggal(inaktifSampai);
            document.getElementById('inaktif_sampai_otomatis').value = inaktifSampai.toISOString().split('T')[0];
        } else {
            inaktifSampaiOtomatisPreview.textContent = '-';
            document.getElementById('inaktif_sampai_otomatis').value = '';
        }
        
        // Hitung status arsip
        let status = hitungStatusArsip(sekarang, aktifSampai, inaktifSampai, keteranganJraOtomatis.value);
        
        // Update status preview
        statusArsipOtomatisPreview.textContent = status;
        statusArsipOtomatisHidden.value = status;
        
        console.log('Perhitungan selesai:', {
            aktifSampai: formatTanggal(aktifSampai),
            inaktifSampai: inaktifSampai ? formatTanggal(inaktifSampai) : '-',
            status: status
        });
    }
    
    function resetPreviewOtomatis() {
        if (aktifSampaiOtomatisPreview) {
            aktifSampaiOtomatisPreview.textContent = '-';
        }
        if (inaktifSampaiOtomatisPreview) {
            inaktifSampaiOtomatisPreview.textContent = '-';
        }
        if (statusArsipOtomatisPreview) {
            statusArsipOtomatisPreview.textContent = 'AKTIF';
        }
        if (statusArsipOtomatisHidden) {
            statusArsipOtomatisHidden.value = 'AKTIF';
        }
    }
    
    // 3. KALKULASI MODE 2: DESKRIPTIF
    const aktifKeterangan = document.getElementById('aktif_keterangan');
    const inaktifKeterangan = document.getElementById('inaktif_keterangan');
    const tanggalReferensi = document.getElementById('tanggal_referensi');
    const keteranganJraDeskriptif = document.getElementById('keterangan_jra_deskriptif');
    
    // Preview elements untuk mode 2
    const aktifSampaiDeskriptifPreview = document.getElementById('aktif_sampai_deskriptif_preview');
    const inaktifSampaiDeskriptifPreview = document.getElementById('inaktif_sampai_deskriptif_preview');
    const statusArsipDeskriptifSelect = document.getElementById('status_arsip_deskriptif_select');
    const statusArsipDeskriptifPreviewContainer = document.getElementById('status_arsip_deskriptif_preview_container');
    const statusArsipDeskriptifPreview = document.getElementById('status_arsip_deskriptif_preview');
    const statusArsipDeskriptifHidden = document.getElementById('status_arsip_deskriptif');
    
    function hitungModeDeskriptif() {
        console.log('hitungModeDeskriptif dipanggil');
        
        // Reset preview
        resetPreviewDeskriptif();
        
        // Jika tanggal referensi kosong, tampilkan dropdown manual
        if (!tanggalReferensi || !tanggalReferensi.value) {
            if (statusArsipDeskriptifSelect) {
                statusArsipDeskriptifSelect.style.display = 'block';
            }
            if (statusArsipDeskriptifPreviewContainer) {
                statusArsipDeskriptifPreviewContainer.style.display = 'none';
            }
            return;
        }
        
        // Jika tanggal referensi diisi, hitung otomatis
        const tanggalRef = new Date(tanggalReferensi.value);
        const sekarang = new Date();
        
        // Ekstrak tahun dari keterangan
        const tahunAktif = ekstrakTahunDariKeterangan(aktifKeterangan.value);
        const tahunInaktif = ekstrakTahunDariKeterangan(inaktifKeterangan.value);
        
        // Hitung aktif sampai
        let aktifSampai = null;
        if (tahunAktif) {
            aktifSampai = new Date(tanggalRef);
            aktifSampai.setFullYear(tanggalRef.getFullYear() + tahunAktif);
            
            if (aktifSampaiDeskriptifPreview) {
                aktifSampaiDeskriptifPreview.textContent = formatTanggal(aktifSampai);
            }
            document.getElementById('aktif_sampai_deskriptif').value = aktifSampai.toISOString().split('T')[0];
        }
        
        // Hitung inaktif sampai
        let inaktifSampai = null;
        if (tahunInaktif && aktifSampai) {
            inaktifSampai = new Date(aktifSampai);
            inaktifSampai.setFullYear(aktifSampai.getFullYear() + tahunInaktif);
            
            if (inaktifSampaiDeskriptifPreview) {
                inaktifSampaiDeskriptifPreview.textContent = formatTanggal(inaktifSampai);
            }
            document.getElementById('inaktif_sampai_deskriptif').value = inaktifSampai.toISOString().split('T')[0];
        }
        
        // Hitung status arsip
        let status = hitungStatusArsip(sekarang, aktifSampai, inaktifSampai, keteranganJraDeskriptif.value);
        
        // Tampilkan hasil otomatis, sembunyikan dropdown
        if (statusArsipDeskriptifSelect) {
            statusArsipDeskriptifSelect.style.display = 'none';
        }
        if (statusArsipDeskriptifPreviewContainer) {
            statusArsipDeskriptifPreviewContainer.style.display = 'block';
        }
        if (statusArsipDeskriptifPreview) {
            statusArsipDeskriptifPreview.textContent = status;
        }
        if (statusArsipDeskriptifHidden) {
            statusArsipDeskriptifHidden.value = status;
        }
    }
    
    function resetPreviewDeskriptif() {
        if (aktifSampaiDeskriptifPreview) {
            aktifSampaiDeskriptifPreview.textContent = '-';
        }
        if (inaktifSampaiDeskriptifPreview) {
            inaktifSampaiDeskriptifPreview.textContent = '-';
        }
        if (statusArsipDeskriptifPreview) {
            statusArsipDeskriptifPreview.textContent = '-';
        }
        if (statusArsipDeskriptifHidden) {
            statusArsipDeskriptifHidden.value = '';
        }
    }
    
    // 4. FUNGSI BANTUAN UMUM
    function formatTanggal(date) {
        if (!date) return '-';
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
    }
    
    function ekstrakTahunDariKeterangan(keterangan) {
        if (!keterangan) return null;
        
        // Cari angka di awal string (contoh: "1 Tahun Setelah..." → 1)
        const match = keterangan.match(/^(\d+)/);
        return match ? parseInt(match[1]) : null;
    }
    
    function hitungStatusArsip(sekarang, aktifSampai, inaktifSampai, keteranganJra) {
        // Rule 1: Jika PERMANEN
        if (keteranganJra === 'PERMANEN') {
            return 'PERMANEN';
        }
        
        // Rule 2: Jika MUSNAH dan sudah lewat inaktif_sampai + 1 tahun
        if (keteranganJra === 'MUSNAH' && inaktifSampai) {
            const tahunSetelahInaktif = new Date(inaktifSampai);
            tahunSetelahInaktif.setFullYear(inaktifSampai.getFullYear() + 1);
            if (sekarang >= tahunSetelahInaktif) {
                return 'USUL_MUSNAH';
            }
        }
        
        // Rule 3: Cek masa aktif/inaktif
        if (aktifSampai && sekarang <= aktifSampai) {
            return 'AKTIF';
        } else if (inaktifSampai && sekarang <= inaktifSampai) {
            return 'INAKTIF';
        } else if (inaktifSampai && sekarang > inaktifSampai) {
            // Sudah lewat masa inaktif
            return 'INAKTIF';
        }
        
        // Default
        return 'AKTIF';
    }
    
    // 5. EVENT LISTENERS UNTUK INPUT CHANGES
    
    // Mode 1 event listeners
    if (tanggalArsip) {
        tanggalArsip.addEventListener('change', function() {
            if (modeTidakKeterangan.checked) {
                hitungModeOtomatis();
            }
        });
    }
    
    if (aktifTahun) {
        aktifTahun.addEventListener('input', function() {
            if (modeTidakKeterangan.checked) {
                hitungModeOtomatis();
            }
        });
    }
    
    if (inaktifTahun) {
        inaktifTahun.addEventListener('input', function() {
            if (modeTidakKeterangan.checked) {
                hitungModeOtomatis();
            }
        });
    }
    
    if (keteranganJraOtomatis) {
        keteranganJraOtomatis.addEventListener('change', function() {
            if (modeTidakKeterangan.checked) {
                hitungModeOtomatis();
            }
        });
    }
    
    // Mode 2 event listeners
    if (aktifKeterangan) {
        aktifKeterangan.addEventListener('input', function() {
            if (modeIsiKeterangan.checked) {
                hitungModeDeskriptif();
            }
        });
    }
    
    if (inaktifKeterangan) {
        inaktifKeterangan.addEventListener('input', function() {
            if (modeIsiKeterangan.checked) {
                hitungModeDeskriptif();
            }
        });
    }
    
    if (tanggalReferensi) {
        tanggalReferensi.addEventListener('change', function() {
            if (modeIsiKeterangan.checked) {
                hitungModeDeskriptif();
            }
        });
    }
    
    if (keteranganJraDeskriptif) {
        keteranganJraDeskriptif.addEventListener('change', function() {
            if (modeIsiKeterangan.checked) {
                hitungModeDeskriptif();
            }
        });
    }
    
    // 6. VALIDASI FILE UPLOAD
    const fileDokumenInput = document.getElementById('file_dokumen');
    if (fileDokumenInput) {
        fileDokumenInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const fileName = file.name;
                const fileExtension = fileName.split('.').pop().toLowerCase();
                
                if (fileSize > 2) {
                    alert('Ukuran file melebihi 2MB');
                    e.target.value = '';
                    return;
                }
                
                const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                if (!allowedExtensions.includes(fileExtension)) {
                    alert('Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.');
                    e.target.value = '';
                    return;
                }
            }
        });
    }
    
    // 7. VALIDASI TAHUN INAKTIF > AKTIF
    function validateYears() {
        if (aktifTahun && aktifTahun.value && inaktifTahun && inaktifTahun.value) {
            if (parseInt(inaktifTahun.value) <= parseInt(aktifTahun.value)) {
                inaktifTahun.setCustomValidity('Tahun inaktif harus lebih besar dari tahun aktif');
            } else {
                inaktifTahun.setCustomValidity('');
            }
        }
    }
    
    if (aktifTahun) {
        aktifTahun.addEventListener('change', validateYears);
    }
    if (inaktifTahun) {
        inaktifTahun.addEventListener('change', validateYears);
    }
    
    // 8. FUNGSI HAPUS FILE
    window.hapusFile = function() {
        if (confirm('Apakah Anda yakin ingin menghapus file ini?')) {
            document.getElementById('hapus_file').value = '1';
            const fileInfo = document.querySelector('.mb-2');
            if (fileInfo) {
                fileInfo.style.display = 'none';
            }
        }
    }
    
    // 9. FUNGSI UNTUK UPDATE STATUS_ARSIP_FINAL
    function updateStatusArsipFinal() {
        let status = 'AKTIF';
        
        if (modeTidakKeterangan.checked) {
            status = document.getElementById('status_arsip_otomatis').value || 'AKTIF';
        } else if (modeIsiKeterangan.checked) {
            if (tanggalReferensi && tanggalReferensi.value) {
                status = document.getElementById('status_arsip_deskriptif').value || 'AKTIF';
            } else {
                status = document.getElementById('status_arsip_deskriptif_select').value || 'AKTIF';
            }
        }
        
        document.getElementById('status_arsip_final').value = status;
        console.log('status_arsip_final diisi: ' + status);
    }
    
    // 10. UPDATE STATUS SAAT MODE BERUBAH
    modeTidakKeterangan.addEventListener('change', function() {
        setTimeout(updateStatusArsipFinal, 100);
    });
    modeIsiKeterangan.addEventListener('change', function() {
        setTimeout(updateStatusArsipFinal, 100);
    });
    
    // 11. UPDATE STATUS SAAT INPUT BERUBAH
    [tanggalArsip, aktifTahun, inaktifTahun, tanggalReferensi, aktifKeterangan, inaktifKeterangan].forEach(el => {
        if (el) {
            el.addEventListener('input', updateStatusArsipFinal);
            el.addEventListener('change', updateStatusArsipFinal);
        }
    });
    
    // 12. INITIALIZATION
    // Hitung awal jika ada data
    setTimeout(function() {
        if (modeTidakKeterangan.checked && aktifTahun && aktifTahun.value) {
            hitungModeOtomatis();
        }
        if (modeIsiKeterangan.checked && tanggalReferensi && tanggalReferensi.value) {
            hitungModeDeskriptif();
        }
        
        // Jalankan validasi tahun
        validateYears();
        
        // Update status arsip final
        updateStatusArsipFinal();
    }, 200);
    
    console.log('JavaScript loaded successfully');

        modeIsiKeterangan.addEventListener('change', function () {
        setTimeout(updateStatusArsipFinal, 100);
    });

    // Update status sebelum submit
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function () {
            updateStatusArsipFinal();
        });
    }

});
</script>
@endsection