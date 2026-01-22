@extends('layouts.app')

@section('page-title', 'Tambah Arsip Baru')
@section('page-subtitle', 'Form Tambah Arsip Digital')

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Form Tambah Arsip</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('arsip.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <!-- Kode Klasifikasi -->
                <div class="col-md-6 mb-3">
                    <label for="kode_klasifikasi_id" class="form-label">Kode Klasifikasi <span class="text-danger">*</span></label>
                    <select class="form-control @error('kode_klasifikasi_id') is-invalid @enderror" 
                            id="kode_klasifikasi_id" name="kode_klasifikasi_id" required>
                        <option value="">Pilih Kode Klasifikasi</option>
                        @foreach($kodeKlasifikasiOptions as $kode)
                            <option value="{{ $kode->id }}" {{ old('kode_klasifikasi_id') == $kode->id ? 'selected' : '' }}>
                                {{ $kode->kode }} - {{ $kode->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('kode_klasifikasi_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Sub Bagian -->
                <div class="col-md-6 mb-3">
                    <label for="sub_bagian_id" class="form-label">Sub Bagian <span class="text-danger">*</span></label>
                    <select class="form-control @error('sub_bagian_id') is-invalid @enderror" 
                            id="sub_bagian_id" name="sub_bagian_id" required>
                        <option value="">Pilih Sub Bagian</option>
                        @foreach($subBagianOptions as $subBagian)
                            <option value="{{ $subBagian->id }}" {{ old('sub_bagian_id') == $subBagian->id ? 'selected' : '' }}>
                                {{ $subBagian->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('sub_bagian_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Judul Arsip -->
                <div class="col-md-12 mb-3">
                    <label for="judul_arsip" class="form-label">Judul Arsip <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('judul_arsip') is-invalid @enderror" 
                           id="judul_arsip" name="judul_arsip" 
                           value="{{ old('judul_arsip') }}" required>
                    @error('judul_arsip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Tahun Arsip -->
                <div class="col-md-3 mb-3">
                    <label for="tahun_arsip" class="form-label">Tahun Arsip <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('tahun_arsip') is-invalid @enderror" 
                           id="tahun_arsip" name="tahun_arsip" 
                           value="{{ old('tahun_arsip', date('Y')) }}" 
                           min="2000" max="{{ date('Y') + 1 }}" required>
                    @error('tahun_arsip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Tanggal Arsip -->
                <div class="col-md-3 mb-3">
                    <label for="tanggal_arsip" class="form-label">Tanggal Arsip</label>
                    <input type="date" class="form-control @error('tanggal_arsip') is-invalid @enderror" 
                           id="tanggal_arsip" name="tanggal_arsip" 
                           value="{{ old('tanggal_arsip') }}">
                    @error('tanggal_arsip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Jumlah Berkas -->
                <div class="col-md-3 mb-3">
                    <label for="jumlah_berkas" class="form-label">Jumlah Berkas</label>
                    <input type="number" class="form-control @error('jumlah_berkas') is-invalid @enderror" 
                           id="jumlah_berkas" name="jumlah_berkas" 
                           value="{{ old('jumlah_berkas', 1) }}" min="1">
                    @error('jumlah_berkas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Satuan Arsip -->
                <div class="col-md-3 mb-3">
                    <label for="satuan_arsip" class="form-label">Satuan Arsip</label>
                    <select class="form-control @error('satuan_arsip') is-invalid @enderror" 
                            id="satuan_arsip" name="satuan_arsip">
                        <option value="">Pilih Satuan</option>
                        <option value="BENDEL" {{ old('satuan_arsip') == 'BENDEL' ? 'selected' : '' }}>BENDEL</option>
                        <option value="LEMBAR" {{ old('satuan_arsip') == 'LEMBAR' ? 'selected' : '' }}>LEMBAR</option>
                    </select>
                    @error('satuan_arsip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Tingkat Perkembangan -->
                <div class="col-md-6 mb-3">
                    <label for="tingkat_perkembangan" class="form-label">Tingkat Perkembangan</label>
                    <select class="form-control @error('tingkat_perkembangan') is-invalid @enderror" 
                            id="tingkat_perkembangan" name="tingkat_perkembangan">
                        <option value="">Pilih Tingkat</option>
                        <option value="ASLI" {{ old('tingkat_perkembangan') == 'ASLI' ? 'selected' : '' }}>ASLI</option>
                        <option value="COPY" {{ old('tingkat_perkembangan') == 'COPY' ? 'selected' : '' }}>COPY</option>
                        <option value="SALINAN" {{ old('tingkat_perkembangan') == 'SALINAN' ? 'selected' : '' }}>SALINAN</option>
                    </select>
                    @error('tingkat_perkembangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Status Arsip -->
                <div class="col-md-6 mb-3">
                    <label for="status_arsip" class="form-label">Status Arsip <span class="text-danger">*</span></label>
                    <select class="form-control @error('status_arsip') is-invalid @enderror" 
                            id="status_arsip" name="status_arsip" required>
                        <option value="">Pilih Status</option>
                        @foreach($statusOptions as $key => $label)
                            <option value="{{ $key }}" {{ old('status_arsip') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status_arsip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Aktif Tahun -->
                <div class="col-md-3 mb-3">
                    <label for="aktif_tahun" class="form-label">Aktif (Tahun) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('aktif_tahun') is-invalid @enderror" 
                           id="aktif_tahun" name="aktif_tahun" 
                           value="{{ old('aktif_tahun') }}" required min="1">
                    @error('aktif_tahun')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Inaktif Tahun -->
                <div class="col-md-3 mb-3">
                    <label for="inaktif_tahun" class="form-label">Inaktif (Tahun) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('inaktif_tahun') is-invalid @enderror" 
                           id="inaktif_tahun" name="inaktif_tahun" 
                           value="{{ old('inaktif_tahun') }}" required>
                    @error('inaktif_tahun')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Nomor Rak -->
                <div class="col-md-3 mb-3">
                    <label for="nomor_rak" class="form-label">Nomor Rak <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nomor_rak') is-invalid @enderror" 
                           id="nomor_rak" name="nomor_rak" 
                           value="{{ old('nomor_rak') }}" required>
                    @error('nomor_rak')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Nomor Box -->
                <div class="col-md-3 mb-3">
                    <label for="nomor_box" class="form-label">Nomor Box <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nomor_box') is-invalid @enderror" 
                           id="nomor_box" name="nomor_box" 
                           value="{{ old('nomor_box') }}" required>
                    @error('nomor_box')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Tanggal Masuk -->
                <div class="col-md-4 mb-3">
                    <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                    <input type="date" class="form-control @error('tanggal_masuk') is-invalid @enderror" 
                           id="tanggal_masuk" name="tanggal_masuk" 
                           value="{{ old('tanggal_masuk', date('Y-m-d')) }}">
                    @error('tanggal_masuk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Keterangan JRA -->
                <div class="col-md-4 mb-3">
                    <label for="keterangan_jra" class="form-label">Keterangan JRA</label>
                    <select class="form-control @error('keterangan_jra') is-invalid @enderror" 
                            id="keterangan_jra" name="keterangan_jra">
                        <option value="">Pilih Keterangan</option>
                        <option value="MUSNAH" {{ old('keterangan_jra') == 'MUSNAH' ? 'selected' : '' }}>MUSNAH</option>
                        <option value="PERMANEN" {{ old('keterangan_jra') == 'PERMANEN' ? 'selected' : '' }}>PERMANEN</option>
                    </select>
                    @error('keterangan_jra')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- File Dokumen -->
                <div class="col-md-4 mb-3">
                    <label for="file_dokumen" class="form-label">File Dokumen</label>
                    <input type="text" class="form-control @error('file_dokumen') is-invalid @enderror" 
                           id="file_dokumen" name="file_dokumen" 
                           value="{{ old('file_dokumen') }}" 
                           placeholder="nama_file.pdf">
                    <small class="text-muted">Isi dengan nama file jika ada</small>
                    @error('file_dokumen')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Keterangan Tambahan -->
                <div class="col-md-12 mb-3">
                    <label for="keterangan_jra" class="form-label">Keterangan Tambahan</label>
                    <textarea class="form-control @error('keterangan_jra') is-invalid @enderror" 
                              id="keterangan_jra" name="keterangan_jra" 
                              rows="3">{{ old('keterangan_jra') }}</textarea>
                    @error('keterangan_jra')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('arsip.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Arsip
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Script untuk validasi tahun inaktif > tahun aktif -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const aktifTahun = document.getElementById('aktif_tahun');
        const inaktifTahun = document.getElementById('inaktif_tahun');
        
        function validateYears() {
            if (aktifTahun.value && inaktifTahun.value) {
                if (parseInt(inaktifTahun.value) <= parseInt(aktifTahun.value)) {
                    inaktifTahun.setCustomValidity('Tahun inaktif harus lebih besar dari tahun aktif');
                } else {
                    inaktifTahun.setCustomValidity('');
                }
            }
        }
        
        aktifTahun.addEventListener('change', validateYears);
        inaktifTahun.addEventListener('change', validateYears);
    });
</script>
@endsection