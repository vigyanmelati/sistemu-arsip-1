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
        @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif
        <form action="{{ route('arsip.update', $arsip->id) }}" method="POST" enctype="multipart/form-data" id="arsipForm">
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
                        value="{{ old('tanggal_arsip', $arsip->tanggal_arsip_for_input) }}" required>
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
                <!-- Kondisi Fisik -->
                <div class="col-md-6 mb-3">
                    <label for="media_arsip" class="form-label">Media Arsip</label>
                      <select name="media_arsip" class="form-control">
                    <option value="TEKSTUAL" {{ $arsip->media_arsip == 'TEKSTUAL' ? 'selected' : '' }}>Tekstual</option>
                    <option value="DIGITAL" {{ $arsip->media_arsip == 'DIGITAL' ? 'selected' : '' }}>Digital</option>
                </select>
                    @error('media_arsip')
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
            <h6 class="mb-3 text-primary">Masa Retensi Arsip</h6>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Masa Retensi Aktif
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                        id="aktif_tahun"
                        name="aktif_tahun"
                        class="form-control"
                        placeholder="Contoh: 2 TAHUN / 2 TAHUN SETELAH KEGIATAN"
                        value="{{ old('aktif_tahun', $arsip->aktif_tahun) }}"
                        >
                    <small class="text-muted">
                        Gunakan kata <strong>SETELAH</strong> jika berbasis tanggal referensi
                    </small>
                    @error('aktif_tahun')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Masa Retensi Inaktif
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                        id="inaktif_tahun"
                        name="inaktif_tahun"
                        class="form-control"
                        placeholder="Contoh: 3 TAHUN / 3 TAHUN SETELAH KEGIATAN"
                        value="{{ old('inaktif_tahun', $arsip->inaktif_tahun) }}"
                       >
                    @error('inaktif_tahun')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3" id="tanggal_referensi_wrapper" style="{{ (stripos($arsip->aktif_tahun, 'SETELAH') !== false || stripos($arsip->inaktif_tahun, 'SETELAH') !== false) ? 'display:block;' : 'display:none;' }}">
                    <label class="form-label">
                        Tanggal Referensi
                    </label>
                    <input type="date"
                        id="tanggal_referensi"
                        name="tanggal_referensi"
                        class="form-control"
                        value="{{ old('tanggal_referensi', $arsip->tanggal_referensi_for_input) }}">
                    <small class="text-muted">
                        Diisi jika masa retensi mengandung kata <strong>SETELAH</strong> (Opsional)
                    </small>
                    @error('tanggal_referensi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Keterangan JRA -->
                <div class="col-md-4 mb-3">
                    <label for="keterangan_jra" class="form-label">Keterangan JRA <span class="text-danger">*</span></label>
                    <select class="form-control @error('keterangan_jra') is-invalid @enderror" 
                            id="keterangan_jra" name="keterangan_jra">
                        <option value="">Pilih Keterangan</option>
                        <option value="PERMANEN" {{ old('keterangan_jra', $arsip->keterangan_jra) == 'PERMANEN' ? 'selected' : '' }}>Permanen</option>
                        <option value="MUSNAH" {{ old('keterangan_jra', $arsip->keterangan_jra) == 'MUSNAH' ? 'selected' : '' }}>Musnah</option>
                    </select>
                    @error('keterangan_jra')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- HASIL PERHITUNGAN RETENSI (HIDDEN) -->
            <input type="hidden" name="aktif_sampai" id="aktif_sampai" value="{{ old('aktif_sampai', $arsip->aktif_sampai) }}">
            <input type="hidden" name="inaktif_sampai" id="inaktif_sampai" value="{{ old('inaktif_sampai', $arsip->inaktif_sampai) }}">
            <input type="hidden" name="status_arsip" id="status_arsip_final" value="{{ old('status_arsip', $arsip->status_arsip) }}">

            <!-- PREVIEW PERHITUNGAN -->
            <div class="alert alert-info" id="previewRetensi" style="display:none;">
                <h6>Preview Perhitungan Retensi:</h6>
                <p id="previewText"></p>
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

                <!-- Lokasi Arsip -->
                <div class="col-md-3 mb-3">
                    <label for="lokasi_arsip" class="form-label">Lokasi Arsip</label>
                    <select class="form-control @error('lokasi_arsip') is-invalid @enderror" 
                        id="lokasi_arsip" name="lokasi_arsip">
                    <option value="">Pilih Lokasi</option>

                    <option value="SUB_BAGIAN" 
                        {{ old('lokasi_arsip', $arsip->lokasi_arsip) == 'SUB_BAGIAN' ? 'selected' : '' }}>
                        Ruang Sub Bagian
                    </option>

                    <option value="RECORD_CENTER_PERMANEN" 
                        {{ old('lokasi_arsip', $arsip->lokasi_arsip) == 'RECORD_CENTER_PERMANEN' ? 'selected' : '' }}>
                        Record Center (Arsip Permanen)
                    </option>

                    <option value="RECORD_CENTER_INAKTIF" 
                        {{ old('lokasi_arsip', $arsip->lokasi_arsip) == 'RECORD_CENTER_INAKTIF' ? 'selected' : '' }}>
                        Record Center (Arsip Inaktif)
                    </option>
                </select>
                    @error('lokasi_arsip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Tanggal Masuk -->
                <div class="col-md-3 mb-3">
                    <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                    <input type="date" class="form-control @error('tanggal_masuk') is-invalid @enderror" 
                           id="tanggal_masuk" name="tanggal_masuk" 
                           value="{{ old('tanggal_masuk', $arsip->tanggal_masuk_for_input) }}">
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

            <hr class="my-4">

<div class="card border-danger">
    <div class="card-header bg-danger text-white">
        <strong>Penanganan Arsip Duplikat</strong>
    </div>

    <div class="card-body">
        <div class="row align-items-center">

            <!-- Checkbox -->
            <div class="col-md-4 mb-3">
                <div class="form-check mt-2">
                    <input class="form-check-input" 
                        type="checkbox" 
                        id="tangani_duplikat" 
                        name="tangani_duplikat"
                        value="1">

                    <label class="form-check-label fw-bold">
                        Ubah status menjadi <span class="text-danger">NON ARSIP</span>
                    </label>

                    <small class="d-block text-muted">
                        Gunakan jika arsip ini terbukti duplikat dan tidak digunakan
                    </small>
                </div>
            </div>

            <!-- Alasan -->
            <div class="col-md-8 mb-3">
                <label class="form-label fw-bold">Alasan Penanganan</label>
                <textarea name="duplicate_reason"
                    id="duplicate_reason"
                    class="form-control"
                    rows="3"
                    placeholder="Tuliskan alasan kenapa arsip ini dijadikan non arsip...">{{ old('duplicate_reason') }}</textarea>
                
                <small class="text-muted">
                    Wajib diisi jika status diubah menjadi Non Arsip
                </small>
            </div>

        </div>
    </div>
</div>
            
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('arsipForm');
    const aktifInput = document.getElementById('aktif_tahun');
    const inaktifInput = document.getElementById('inaktif_tahun');
    const tanggalArsipInput = document.getElementById('tanggal_arsip');
    const tanggalRefInput = document.getElementById('tanggal_referensi');
    const wrapperRef = document.getElementById('tanggal_referensi_wrapper');
    const keteranganJRA = document.getElementById('keterangan_jra');
    const aktifSampaiInput = document.getElementById('aktif_sampai');
    const inaktifSampaiInput = document.getElementById('inaktif_sampai');
    const statusArsipInput = document.getElementById('status_arsip_final');
    const previewRetensi = document.getElementById('previewRetensi');
    const previewText = document.getElementById('previewText');

    function cekSetelah() {
        const aktifVal = aktifInput.value.toLowerCase();
        const inaktifVal = inaktifInput.value.toLowerCase();

        if (aktifVal.includes('setelah') || inaktifVal.includes('setelah')) {
            wrapperRef.style.display = 'block';
        } else {
            wrapperRef.style.display = 'none';
            if (tanggalRefInput) {
                tanggalRefInput.value = '';
            }
        }
        
        hitungRetensi();
    }

    function hitungRetensi() {
        const aktifVal = aktifInput.value.trim().toUpperCase();
        const inaktifVal = inaktifInput.value.trim().toUpperCase();
        const keterangan = keteranganJRA.value;
        const tanggalArsip = tanggalArsipInput.value;
        const tanggalReferensi = tanggalRefInput ? tanggalRefInput.value : '';
        
        if (!aktifVal || !inaktifVal || !tanggalArsip) {
            previewRetensi.style.display = 'none';
            return;
        }

        // LOGIKA UTAMA: Jika aktif_tahun mengandung SETELAH tetapi tanggal_referensi belum diisi
        if ((aktifVal.includes('SETELAH') || inaktifVal.includes('SETELAH')) && !tanggalReferensi) {
            // Otomatis set status arsip ke AKTIF (sesuai permintaan)
            statusArsipInput.value = 'AKTIF';
            
            // Set nilai default untuk aktif_sampai dan inaktif_sampai (null)
            aktifSampaiInput.value = '';
            inaktifSampaiInput.value = '';
            
            // Tampilkan preview khusus
            let preview = `<strong>Perhatian:</strong><br>`;
            preview += `Anda menggunakan format "SETELAH" untuk masa retensi, tetapi belum mengisi tanggal referensi.<br>`;
            preview += `<strong>Status arsip otomatis di-set ke: AKTIF</strong><br>`;
            preview += `Perhitungan retensi tidak dapat dilakukan tanpa tanggal referensi.<br>`;
            preview += `Silakan isi tanggal referensi untuk perhitungan yang lebih akurat.`;
            
            previewText.innerHTML = preview;
            previewRetensi.style.display = 'block';
            return;
        }

        // Ekstrak angka tahun dari input
        const aktifTahun = ekstrakAngka(aktifVal);
        const inaktifTahun = ekstrakAngka(inaktifVal);
        
        if (!aktifTahun || !inaktifTahun) {
            previewText.textContent = 'Format tahun tidak valid. Contoh: "2 TAHUN"';
            previewRetensi.style.display = 'block';
            return;
        }

        // Tentukan tanggal dasar perhitungan
        let tanggalDasar;
        let sumberTanggal = 'tanggal_arsip';
        
        if (aktifVal.includes('SETELAH') || inaktifVal.includes('SETELAH')) {
            tanggalDasar = tanggalReferensi;
            sumberTanggal = 'tanggal_referensi';
        } else {
            tanggalDasar = tanggalArsip;
        }

        // Hitung tanggal aktif sampai
        const aktifSampai = tambahTahun(tanggalDasar, aktifTahun);
        
        // Hitung tanggal inaktif sampai (ditambahkan setelah aktif)
        const inaktifSampai = tambahTahun(aktifSampai, inaktifTahun);

        // Hitung tanggal musnah (hanya untuk display jika keterangan MUSNAH)
        const musnahSampai = tambahTahun(inaktifSampai, 1);

        // Tentukan status arsip berdasarkan tanggal hari ini
        const sekarang = new Date();
        const aktifDate = new Date(aktifSampai);
        const inaktifDate = new Date(inaktifSampai);
        const musnahDate = new Date(musnahSampai);

        let statusArsip = 'AKTIF';
        let statusText = '';
        
        if (keterangan === 'PERMANEN') {
            statusArsip = 'PERMANEN';
            statusText = 'Status Arsip saat ini: PERMANEN';
        } else if (keterangan === 'MUSNAH') {
            // Tentukan status saat ini berdasarkan tanggal
            if (sekarang <= aktifDate) {
                statusArsip = 'AKTIF';
                statusText = `Status Arsip saat ini: AKTIF`;
            } else if (sekarang <= inaktifDate) {
                statusArsip = 'INAKTIF';
                statusText = `Status Arsip saat ini: INAKTIF`;
            } else if (sekarang <= musnahDate) {
                statusArsip = 'MUSNAH';
                statusText = `Status Arsip saat ini: USUL MUSNAH`;
            } else {
                statusArsip = 'MUSNAH';
                statusText = `Status Arsip saat ini: USUL MUSNAH (telah lewat)`;
            }
        } else {
            // Jika keterangan tidak ada, gunakan logika biasa
            if (sekarang <= aktifDate) {
                statusArsip = 'AKTIF';
                statusText = `Status Arsip saat ini: AKTIF`;
            } else if (sekarang <= inaktifDate) {
                statusArsip = 'INAKTIF';
                statusText = `Status Arsip saat ini: INAKTIF`;
            } else {
                statusArsip = 'INAKTIF';
                statusText = `Status Arsip saat ini: INAKTIF (telah lewat)`;
            }
        }

        // Set nilai hidden inputs
        aktifSampaiInput.value = aktifSampai;
        inaktifSampaiInput.value = inaktifSampai;
        statusArsipInput.value = statusArsip;

        // Tampilkan preview dengan satu status saja
        let preview = `<strong>Perhitungan Retensi:</strong><br>`;
        preview += `Sumber Tanggal: ${sumberTanggal === 'tanggal_referensi' ? 'Tanggal Referensi' : 'Tanggal Arsip'}<br>`;
        preview += `Tanggal Dasar: ${formatTanggal(tanggalDasar)}<br>`;
        preview += `Aktif Sampai: ${formatTanggal(aktifSampai)}<br>`;
        preview += `Inaktif Sampai: ${formatTanggal(inaktifSampai)}<br>`;
        
        if (keterangan === 'MUSNAH') {
            preview += `Musnah: ${formatTanggal(musnahSampai)}<br>`;
        }
        
        preview += `<strong>${statusText}</strong>`;
        
        previewText.innerHTML = preview;
        previewRetensi.style.display = 'block';
    }

    function ekstrakAngka(text) {
        const match = text.match(/\d+/);
        return match ? parseInt(match[0]) : null;
    }

    function tambahTahun(tanggalString, tahun) {
        const date = new Date(tanggalString);
        date.setFullYear(date.getFullYear() + tahun);
        return date.toISOString().split('T')[0];
    }

    function formatTanggal(tanggalString) {
        const date = new Date(tanggalString);
        return date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    // Event listeners
    aktifInput.addEventListener('input', cekSetelah);
    inaktifInput.addEventListener('input', cekSetelah);
    tanggalArsipInput.addEventListener('change', hitungRetensi);
    if (tanggalRefInput) {
        tanggalRefInput.addEventListener('change', hitungRetensi);
    }
    keteranganJRA.addEventListener('change', hitungRetensi);

    // Form submission validation
    form.addEventListener('submit', function(e) {
        hitungRetensi(); // Pastikan perhitungan terakhir
        
        // Validasi minimal
        // if (!keteranganJRA.value) {
        //     e.preventDefault();
        //     alert('Silakan pilih Keterangan JRA.');
        //     return;
        // }
        
        // Validasi file upload (opsional)
        const fileInput = document.getElementById('file_dokumen');
        if (fileInput && fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            const fileName = file.name;
            const fileExtension = fileName.split('.').pop().toLowerCase();
            
            if (fileSize > 2) {
                e.preventDefault();
                alert('Ukuran file melebihi 2MB');
                return;
            }
            
            const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
            if (!allowedExtensions.includes(fileExtension)) {
                e.preventDefault();
                alert('Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.');
                return;
            }
        }
    });

    // Fungsi untuk menghapus file
    window.hapusFile = function() {
        if (confirm('Apakah Anda yakin ingin menghapus file ini?')) {
            document.getElementById('hapus_file').value = '1';
            const fileInfoDiv = document.querySelector('.mb-2');
            if (fileInfoDiv) {
                fileInfoDiv.style.display = 'none';
            }
        }
    }

    const tanganiCheckbox = document.getElementById('tangani_duplikat');
    const alasanDuplicate = document.getElementById('duplicate_reason');

    form.addEventListener('submit', function(e) {
        if (tanganiCheckbox.checked && !alasanDuplicate.value.trim()) {
            e.preventDefault();
            alert('Alasan wajib diisi untuk penanganan duplikat');
            return;
        }
    });

    // Initial load - tampilkan preview berdasarkan data yang ada
    setTimeout(function() {
        // Jalankan cekSetelah untuk menampilkan/menyembunyikan tanggal referensi
        cekSetelah();
        
        // Jalankan hitungRetensi untuk menampilkan preview
        hitungRetensi();
    }, 300);
});
</script>
@endpush