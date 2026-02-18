{{-- resources/views/subbagian/riwayat-pemindahan/edit-perbaikan.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Perbaikan Arsip')
@section('page-subtitle', 'Edit Data Arsip dan Upload Berita Acara Baru')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">
                        <i class="fas fa-wrench mr-2"></i>Perbaikan Arsip
                    </h3>
                </div>
                <div class="card-body">
                    @if($arsip->catatan_verifikasi)
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle mr-2"></i>Alasan Penolakan:</h5>
                        <p class="mb-0">{{ $arsip->catatan_verifikasi }}</p>
                        @if($arsip->tanggal_diverifikasi)
                        <small class="d-block mt-2">
                            Diverifikasi pada: {{ \Carbon\Carbon::parse($arsip->tanggal_diverifikasi)->format('d F Y H:i') }}
                        </small>
                        @endif
                    </div>
                    @endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
                    <form action="{{ route('subbagian.riwayat-pemindahan.update-perbaikan', $arsip->id) }}" 
                          method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Informasi Klasifikasi -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Informasi Klasifikasi</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Kode Klasifikasi *</label>
                                            <select name="kode_klasifikasi_id" class="form-control" required>
                                                <option value="">Pilih Kode Klasifikasi</option>
                                                @foreach($kodeKlasifikasis as $kode)
                                                <option value="{{ $kode->id }}" 
                                                    {{ $arsip->kode_klasifikasi_id == $kode->id ? 'selected' : '' }}>
                                                    {{ $kode->kode }} - {{ $kode->uraian }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Uraian Arsip *</label>
                                            <input type="text" name="uraian_arsip" 
                                                   class="form-control" 
                                                   value="{{ old('uraian_arsip', $arsip->uraian_arsip) }}" 
                                                   required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tahun Arsip *</label>
                                            <input type="number" name="tahun_arsip" 
                                                   class="form-control" 
                                                   value="{{ old('tahun_arsip', $arsip->tahun_arsip) }}" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tanggal Arsip *</label>
                                            <input type="date" name="tanggal_arsip" 
                                                   class="form-control" 
                                                   value="{{ old('tanggal_arsip', $arsip->tanggal_arsip_for_input) }}" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Sub Bagian</label>
                                            <input type="text" class="form-control bg-light" 
                                                   value="{{ $arsip->subBagian->nama_sub_bagian }}" 
                                                   readonly>
                                            <input type="hidden" name="sub_bagian_id" 
                                                   value="{{ $arsip->sub_bagian_id }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Fisik -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Informasi Fisik Arsip</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Jumlah Berkas *</label>
                                            <input type="number" name="jumlah_berkas" 
                                                   class="form-control" 
                                                   value="{{ old('jumlah_berkas', $arsip->jumlah_berkas) }}" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Satuan *</label>
                                            <select name="satuan_arsip" class="form-control" required>
                                                <option value="LEMBAR" {{ $arsip->satuan_arsip == 'LEMBAR' ? 'selected' : '' }}>LEMBAR</option>
                                                <option value="BUNDEL" {{ $arsip->satuan_arsip == 'BUNDEL' ? 'selected' : '' }}>BUNDEL</option>
                                                <option value="DOSSIER" {{ $arsip->satuan_arsip == 'DOSSIER' ? 'selected' : '' }}>DOSSIER</option>
                                                <option value="BUKU" {{ $arsip->satuan_arsip == 'BUKU' ? 'selected' : '' }}>BUKU</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Tingkat Perkembangan *</label>
                                            <select name="tingkat_perkembangan" class="form-control" required>
                                                <option value="ASLI" {{ $arsip->tingkat_perkembangan == 'ASLI' ? 'selected' : '' }}>ASLI</option>
                                                <option value="SALINAN" {{ $arsip->tingkat_perkembangan == 'SALINAN' ? 'selected' : '' }}>SALINAN</option>
                                                <option value="MINUTA" {{ $arsip->tingkat_perkembangan == 'MINUTA' ? 'selected' : '' }}>MINUTA</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Keterangan *</label>
                                            <select name="keterangan" class="form-control" required>
                                                <option value="BAIK" {{ $arsip->keterangan == 'BAIK' ? 'selected' : '' }}>BAIK</option>
                                                <option value="RUSAK RINGAN" {{ $arsip->keterangan == 'RUSAK RINGAN' ? 'selected' : '' }}>RUSAK RINGAN</option>
                                                <option value="RUSAK BERAT" {{ $arsip->keterangan == 'RUSAK BERAT' ? 'selected' : '' }}>RUSAK BERAT</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Media Arsip *</label>
                                            <select name="media_arsip" class="form-control" required>
                                                <option value="TEKSTUAL" {{ $arsip->media_arsip == 'TEKSTUAL' ? 'selected' : '' }}>TEKSTUAL</option>
                                                <option value="ELEKTRONIK" {{ $arsip->media_arsip == 'ELEKTRONIK' ? 'selected' : '' }}>ELEKTRONIK</option>
                                                <option value="AUDIO VISUAL" {{ $arsip->media_arsip == 'AUDIO VISUAL' ? 'selected' : '' }}>AUDIO VISUAL</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Nomor Rak</label>
                                            <input type="text" name="nomor_rak" 
                                                   class="form-control" 
                                                   value="{{ old('nomor_rak', $arsip->nomor_rak) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Nomor Box</label>
                                            <input type="text" name="nomor_box" 
                                                   class="form-control" 
                                                   value="{{ old('nomor_box', $arsip->nomor_box) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Retensi -->
                        <!-- <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Informasi Retensi</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Masa Aktif *</label>
                                            <input type="text" name="aktif_tahun" 
                                                   class="form-control" 
                                                   value="{{ old('aktif_tahun', $arsip->aktif_tahun) }}" 
                                                   placeholder="Contoh: 2 TAHUN" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Masa Inaktif *</label>
                                            <input type="text" name="inaktif_tahun" 
                                                   class="form-control" 
                                                   value="{{ old('inaktif_tahun', $arsip->inaktif_tahun) }}" 
                                                   placeholder="Contoh: 5 TAHUN" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Tanggal Referensi</label>
                                            <input type="date" name="tanggal_referensi" 
                                                   class="form-control" 
                                                   value="{{ old('tanggal_referensi', $arsip->tanggal_referensi_for_input) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Keterangan JRA *</label>
                                            <select name="keterangan_jra" class="form-control" required>
                                                <option value="BELUM DITENTUKAN" {{ $arsip->keterangan_jra == 'BELUM DITENTUKAN' ? 'selected' : '' }}>BELUM DITENTUKAN</option>
                                                <option value="MUSNAH" {{ $arsip->keterangan_jra == 'MUSNAH' ? 'selected' : '' }}>MUSNAH</option>
                                                <option value="PERMANEN" {{ $arsip->keterangan_jra == 'PERMANEN' ? 'selected' : '' }}>PERMANEN</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <!-- Berita Acara dan Catatan -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Dokumen dan Catatan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Upload Berita Acara Baru *</label>
                                            <div class="custom-file">
                                                <input type="file" name="file_berita_acara_baru" 
                                                       class="custom-file-input" id="fileInput" >
                                                <label class="custom-file-label" for="fileInput">
                                                    Pilih file berita acara...
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">
                                                Format: PDF, JPG, JPEG, PNG (Maks: 2MB)
                                            </small>
                                            @if($arsip->file_berita_acara)
                                            <div class="mt-2">
                                                <small class="text-info">
                                                    <i class="fas fa-info-circle"></i> 
                                                    File sebelumnya: {{ $arsip->file_berita_acara }}
                                                </small>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Catatan Perbaikan *</label>
                                            <textarea name="catatan_perbaikan" 
                                                      class="form-control" 
                                                      rows="4" 
                                                      placeholder="Jelaskan perbaikan apa yang telah dilakukan berdasarkan alasan penolakan..." 
                                                      required>{{ old('catatan_perbaikan') }}</textarea>
                                            <small class="form-text text-muted">
                                                Jelaskan perbaikan yang telah dilakukan untuk mengatasi alasan penolakan.
                                            </small>
                                        </div>
                                    </div>
                                </div> -->
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <a href="{{ route('subbagian.riwayat-pemindahan.show', $arsip->id) }}" 
                                           class="btn btn-secondary btn-lg">
                                            <i class="fas fa-arrow-left mr-2"></i> Kembali
                                        </a>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <button type="submit" class="btn btn-warning btn-lg">
                                            <i class="fas fa-save mr-2"></i> Simpan Perbaikan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Update nama file saat dipilih
    document.querySelector('#fileInput').addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
</script>
@endpush