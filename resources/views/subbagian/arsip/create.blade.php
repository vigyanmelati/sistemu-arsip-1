@extends('layouts.app')

@section('page-title', 'Tambah Arsip Baru')
@section('page-subtitle', 'Form Tambah Arsip Digital')

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Form Tambah Arsip</h6>
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
        <form action="{{ route('subbagian.arsip.store') }}" method="POST" enctype="multipart/form-data" id="arsipForm">
            @csrf
            
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
                            <option value="{{ $kode->id }}" {{ old('kode_klasifikasi_id') == $kode->id ? 'selected' : '' }}>
                                {{ $kode->kode }} - {{ $kode->uraian }}
                            </option>
                        @endforeach
                    </select>
                    @error('kode_klasifikasi_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
               <!-- Sub Bagian (Auto Sesuai User) -->
                <input type="hidden" name="sub_bagian_id" value="{{ auth()->user()->sub_bagian_id }}">

                
                <!-- Judul/Uraian Arsip (WAJIB) -->
                <div class="col-md-12 mb-3">
                    <label for="uraian_arsip" class="form-label">Uraian/Judul Arsip <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('uraian_arsip') is-invalid @enderror" 
                        id="uraian_arsip" name="uraian_arsip" 
                        value="{{ old('uraian_arsip') }}" required>
                    @error('uraian_arsip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Tahun Arsip (WAJIB) -->
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
                
                <!-- Tanggal Arsip (WAJIB) -->
                <div class="col-md-3 mb-3">
                    <label for="tanggal_arsip" class="form-label">Tanggal Arsip <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('tanggal_arsip') is-invalid @enderror" 
                        id="tanggal_arsip" name="tanggal_arsip" 
                        value="{{ old('tanggal_arsip', date('Y-m-d')) }}" required>
                    @error('tanggal_arsip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Jumlah Berkas (WAJIB) -->
                <div class="col-md-3 mb-3">
                    <label for="jumlah_berkas" class="form-label">Jumlah Berkas <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('jumlah_berkas') is-invalid @enderror" 
                        id="jumlah_berkas" name="jumlah_berkas" 
                        value="{{ old('jumlah_berkas', 1) }}" min="1" required>
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
                        <option value="BENDEL" {{ old('satuan_arsip', 'LEMBAR') == 'BENDEL' ? 'selected' : '' }}>BENDEL</option>
                        <option value="LEMBAR" {{ old('satuan_arsip', 'LEMBAR') == 'LEMBAR' ? 'selected' : '' }}>LEMBAR</option>
                    </select>
                    @error('satuan_arsip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Kondisi Fisik -->
                <div class="col-md-6 mb-3">
                    <label for="keterangan" class="form-label">Kondisi Fisik <span class="text-danger">*</span></label>
                    <select class="form-control @error('keterangan') is-invalid @enderror" 
                            id="keterangan" name="keterangan">
                        <option value="">Pilih Kondisi</option>
                        <option value="BAIK" {{ old('keterangan') == 'BAIK' ? 'selected' : '' }}>Baik</option>
                        <option value="RUSAK" {{ old('keterangan') == 'RUSAK' ? 'selected' : '' }}>Rusak</option>
                        <option value="HILANG" {{ old('keterangan') == 'HILANG' ? 'selected' : '' }}>Hilang</option>
                    </select>
                    @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="media_arsip" class="form-label">Media Arsip <span class="text-danger">*</span></label>
                    <select name="media_arsip" id="media_arsip" class="form-control">
                        <option value="">Pilih Media</option>
                        <option value="TEKSTUAL" {{ old('media_arsip') == 'TEKSTUAL' ? 'selected' : '' }}>Tekstual</option>
                        <option value="DIGITAL" {{ old('media_arsip') == 'DIGITAL' ? 'selected' : '' }}>Digital</option>
                    </select>
                </div>
<!-- Klasifikasi Keamanan -->
<div class="col-md-6 mb-3">
    <label for="klasifikasi_keamanan" class="form-label">
        Klasifikasi Keamanan <span class="text-danger">*</span>
    </label>
    <select class="form-control @error('klasifikasi_keamanan') is-invalid @enderror"
            id="klasifikasi_keamanan"
            name="klasifikasi_keamanan"
            required>
        <option value="">Pilih Klasifikasi</option>
        <option value="Biasa/Terbuka" {{ old('klasifikasi_keamanan') == 'BIASA/TERBUKA' ? 'selected' : '' }}>
            Biasa/Terbuka
        </option>
        <option value="Terbatas" {{ old('klasifikasi_keamanan') == 'TERBATAS' ? 'selected' : '' }}>
            Terbatas
        </option>
        <option value="Rahasia" {{ old('klasifikasi_keamanan') == 'RAHASIA' ? 'selected' : '' }}>
            Rahasia
        </option>
    </select>

    @error('klasifikasi_keamanan')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

                 <!-- Tingkat Perkembangan -->
                <div class="col-md-6 mb-3">
                    <label for="tingkat_perkembangan" class="form-label">Tingkat Perkembangan <span class="text-danger">*</span></label>
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
            </div>
            
            <hr class="my-4">
            
            <!-- MODE PENGISIAN RETENSI -->
           
            
            <!-- INFORMASI TAMBAHAN (OPTIONAL) -->
            <h6 class="mb-3 text-primary">Informasi Tambahan (Opsional)</h6>
            
            <div class="row">
                <!-- Nomor Rak -->
                <div class="col-md-3 mb-3">
                    <label for="nomor_rak" class="form-label">Nomor Rak</label>
                    <input type="text" class="form-control @error('nomor_rak') is-invalid @enderror" 
                           id="nomor_rak" name="nomor_rak" 
                           value="{{ old('nomor_rak') }}" 
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
                           value="{{ old('nomor_box') }}" 
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
                        value="{{ old('nomor_sampul') }}" 
                        placeholder="Misal: 1">
                    @error('nomor_sampul')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    {{-- <label for="lokasi_arsip" class="form-label">Lokasi Arsip</label> --}}
                    <input type="hidden" name="lokasi_arsip" value="SUB_BAGIAN">
                    {{-- <div class="col-md-6 mb-3">
                        <label class="form-label">Lokasi Arsip</label>
                        <input type="text" class="form-control" value="Ruang Sub Bagian" readonly>
                    </div> --}}
                    @error('lokasi_arsip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                                
                
            </div>
            
            <div class="row">
                <!-- FILE UPLOAD -->
                <div class="col-md-6 mb-3">
                    <label for="file_dokumen" class="form-label">File Dokumen</label>
                    <input type="file" class="form-control @error('file_dokumen') is-invalid @enderror" 
                           id="file_dokumen" name="file_dokumen" 
                           accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">
                        Format: PDF, JPG, JPEG, PNG (Maks: 10MB) - Opsional
                    </small>
                    @error('file_dokumen')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                  <!-- LINK FOTO -->
    <div class="col-md-6 mb-3">
        <label for="link_foto" class="form-label">Link Foto / URL Dokumen</label>

        <input type="url"
               class="form-control @error('link_foto') is-invalid @enderror"
               id="link_foto"
               name="link_foto"
               value="{{ old('link_foto') }}"
               placeholder="https://drive.google.com/file/d/...">

        <small class="text-muted">
            Opsional. Isi jika file disimpan di Google Drive, OneDrive, Dropbox, dll.
        </small>

        @error('link_foto')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
            </div>
            
            <!-- TOMBOL SIMPAN -->
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('subbagian.arsip.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Arsip
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
    // const aktifInput = document.getElementById('aktif_tahun');
    // const inaktifInput = document.getElementById('inaktif_tahun');
    const tanggalArsipInput = document.getElementById('tanggal_arsip');
    // const tanggalRefInput = document.getElementById('tanggal_referensi');
    // const wrapperRef = document.getElementById('tanggal_referensi_wrapper');
    // const keteranganJRA = document.getElementById('keterangan_jra');
    // const aktifSampaiInput = document.getElementById('aktif_sampai');
    // const inaktifSampaiInput = document.getElementById('inaktif_sampai');
    // const statusArsipInput = document.getElementById('status_arsip_final');
    // const previewRetensi = document.getElementById('previewRetensi');
    // const previewText = document.getElementById('previewText');
    const tahunArsipInput = document.getElementById('tahun_arsip');
tanggalArsipInput.addEventListener('change', function () {
    if (this.value) {
        const tahun = new Date(this.value).getFullYear();
        tahunArsipInput.value = tahun;
    }
});
    // function cekSetelah() {
    //     const aktifVal = aktifInput.value.toLowerCase();
    //     const inaktifVal = inaktifInput.value.toLowerCase();

    //     if (aktifVal.includes('setelah') || inaktifVal.includes('setelah')) {
    //         wrapperRef.style.display = 'block';
    //     } else {
    //         wrapperRef.style.display = 'none';
    //         if (tanggalRefInput) {
    //             tanggalRefInput.value = '';
    //         }
    //     }
        
    //     hitungRetensi();
    // }

    // function hitungRetensi() {
    //     const aktifVal = aktifInput.value.trim().toUpperCase();
    //     const inaktifVal = inaktifInput.value.trim().toUpperCase();
    //     const keterangan = keteranganJRA.value;
    //     const tanggalArsip = tanggalArsipInput.value;
    //     const tanggalReferensi = tanggalRefInput ? tanggalRefInput.value : '';
        
    //     if (!aktifVal || !inaktifVal || !tanggalArsip) {
    //         previewRetensi.style.display = 'none';
    //         // default status ke AKTIF
    //         statusArsipInput.value = 'AKTIF';
    //         aktifSampaiInput.value = '';
    //         inaktifSampaiInput.value = '';
    //         return;
    //     }

    //     // LOGIKA UTAMA: Jika aktif_tahun mengandung SETELAH tetapi tanggal_referensi belum diisi
    //     if ((aktifVal.includes('SETELAH') || inaktifVal.includes('SETELAH')) && !tanggalReferensi) {
    //         // Otomatis set status arsip ke AKTIF (sesuai permintaan)
    //         statusArsipInput.value = 'AKTIF';
            
    //         // Set nilai default untuk aktif_sampai dan inaktif_sampai (null)
    //         aktifSampaiInput.value = '';
    //         inaktifSampaiInput.value = '';
            
    //         // Tampilkan preview khusus
    //         let preview = `<strong>Perhatian:</strong><br>`;
    //         preview += `Anda menggunakan format "SETELAH" untuk masa retensi, tetapi belum mengisi tanggal referensi.<br>`;
    //         preview += `<strong>Status arsip otomatis di-set ke: AKTIF</strong><br>`;
    //         preview += `Perhitungan retensi tidak dapat dilakukan tanpa tanggal referensi.<br>`;
    //         preview += `Silakan isi tanggal referensi untuk perhitungan yang lebih akurat.`;
            
    //         previewText.innerHTML = preview;
    //         previewRetensi.style.display = 'block';
    //         return;
    //     }

    //     // Ekstrak angka tahun dari input
    //     const aktifTahun = ekstrakAngka(aktifVal);
    //     const inaktifTahun = ekstrakAngka(inaktifVal);
        
    //     if (!aktifTahun || !inaktifTahun) {
    //         previewText.textContent = 'Format tahun tidak valid. Contoh: "2 TAHUN"';
    //         previewRetensi.style.display = 'block';
    //         return;
    //     }

    //     // Tentukan tanggal dasar perhitungan
    //     let tanggalDasar;
    //     let sumberTanggal = 'tanggal_arsip';
        
    //     if (aktifVal.includes('SETELAH') || inaktifVal.includes('SETELAH')) {
    //         tanggalDasar = tanggalReferensi;
    //         sumberTanggal = 'tanggal_referensi';
    //     } else {
    //         tanggalDasar = tanggalArsip;
    //     }

    //     // Hitung tanggal aktif sampai
    //     const aktifSampai = tambahTahun(tanggalDasar, aktifTahun);
        
    //     // Hitung tanggal inaktif sampai (ditambahkan setelah aktif)
    //     const inaktifSampai = tambahTahun(aktifSampai, inaktifTahun);

    //     // Hitung tanggal musnah (hanya untuk display jika keterangan MUSNAH)
    //     const musnahSampai = tambahTahun(inaktifSampai, 1);

    //     // Tentukan status arsip berdasarkan tanggal hari ini
    //     const sekarang = new Date();
    //     const aktifDate = new Date(aktifSampai);
    //     const inaktifDate = new Date(inaktifSampai);
    //     const musnahDate = new Date(musnahSampai);

    //     let statusArsip = 'AKTIF';
    //     let statusText = '';
        
    //     if (keterangan === 'PERMANEN') {
    //         statusArsip = 'PERMANEN';
    //         statusText = 'Status Arsip saat ini: PERMANEN';
    //     } else if (keterangan === 'MUSNAH') {
    //         // Tentukan status saat ini berdasarkan tanggal
    //         if (sekarang <= aktifDate) {
    //             statusArsip = 'AKTIF';
    //             statusText = `Status Arsip saat ini: AKTIF`;
    //         } else if (sekarang <= inaktifDate) {
    //             statusArsip = 'INAKTIF';
    //             statusText = `Status Arsip saat ini: INAKTIF`;
    //         } else if (sekarang <= musnahDate) {
    //             statusArsip = 'MUSNAH';
    //             statusText = `Status Arsip saat ini: HABIS RETENSI`;
    //         } else {
    //             statusArsip = 'MUSNAH';
    //             statusText = `Status Arsip saat ini: HABIS RETENSI (telah lewat)`;
    //         }
    //     } else {
    //         // Jika keterangan tidak ada, gunakan logika biasa
    //         if (sekarang <= aktifDate) {
    //             statusArsip = 'AKTIF';
    //             statusText = `Status Arsip saat ini: AKTIF`;
    //         } else if (sekarang <= inaktifDate) {
    //             statusArsip = 'INAKTIF';
    //             statusText = `Status Arsip saat ini: INAKTIF`;
    //         } else {
    //             statusArsip = 'INAKTIF';
    //             statusText = `Status Arsip saat ini: INAKTIF (telah lewat)`;
    //         }
    //     }

    //     // Set nilai hidden inputs
    //     aktifSampaiInput.value = aktifSampai;
    //     inaktifSampaiInput.value = inaktifSampai;
    //     statusArsipInput.value = statusArsip;

    //     // Tampilkan preview dengan satu status saja
    //     let preview = `<strong>Perhitungan Retensi:</strong><br>`;
    //     preview += `Sumber Tanggal: ${sumberTanggal === 'tanggal_referensi' ? 'Tanggal Referensi' : 'Tanggal Arsip'}<br>`;
    //     preview += `Tanggal Dasar: ${formatTanggal(tanggalDasar)}<br>`;
    //     preview += `Aktif Sampai: ${formatTanggal(aktifSampai)}<br>`;
    //     preview += `Inaktif Sampai: ${formatTanggal(inaktifSampai)}<br>`;
        
    //     if (keterangan === 'MUSNAH') {
    //         preview += `Musnah: ${formatTanggal(musnahSampai)}<br>`;
    //     }
        
    //     preview += `<strong>${statusText}</strong>`;
        
    //     previewText.innerHTML = preview;
    //     previewRetensi.style.display = 'block';
    // }

    // function ekstrakAngka(text) {
    //     const match = text.match(/\d+/);
    //     return match ? parseInt(match[0]) : null;
    // }

    // function tambahTahun(tanggalString, tahun) {
    //     const date = new Date(tanggalString);
    //     date.setFullYear(date.getFullYear() + tahun);
    //     return date.toISOString().split('T')[0];
    // }

    // function formatTanggal(tanggalString) {
    //     const date = new Date(tanggalString);
    //     return date.toLocaleDateString('id-ID', {
    //         year: 'numeric',
    //         month: 'long',
    //         day: 'numeric'
    //     });
    // }

    // // Event listeners
    // aktifInput.addEventListener('input', cekSetelah);
    // inaktifInput.addEventListener('input', cekSetelah);
    // tanggalArsipInput.addEventListener('change', hitungRetensi);
    // if (tanggalRefInput) {
    //     tanggalRefInput.addEventListener('change', hitungRetensi);
    // }
    // keteranganJRA.addEventListener('change', hitungRetensi);

    // // Form submission validation
    // form.addEventListener('submit', function(e) {
    //     hitungRetensi(); // Pastikan perhitungan terakhir
        
    //     // Validasi minimal
    //     // if (!keteranganJRA.value) {
    //     //     e.preventDefault();
    //     //     alert('Silakan pilih Keterangan JRA.');
    //     //     return;
    //     // }
        
    //     // Validasi file upload (opsional)
    //     const fileInput = document.getElementById('file_dokumen');
    //     if (fileInput && fileInput.files.length > 0) {
    //         const file = fileInput.files[0];
    //         const fileSize = (file.size / 1024 / 1024).toFixed(2);
    //         const fileName = file.name;
    //         const fileExtension = fileName.split('.').pop().toLowerCase();
            
    //         if (fileSize > 2) {
    //             e.preventDefault();
    //             alert('Ukuran file melebihi 2MB');
    //             return;
    //         }
            
    //         const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
    //         if (!allowedExtensions.includes(fileExtension)) {
    //             e.preventDefault();
    //             alert('Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.');
    //             return;
    //         }
    //     }
        
    // });


    // Initial load
    // cekSetelah();
});
</script>
@endpush