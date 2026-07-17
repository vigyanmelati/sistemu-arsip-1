@extends('layouts.app')

@section('page-title', 'Detail Arsip Masuk')
@section('page-subtitle', 'Verifikasi Kelengkapan Arsip')

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Informasi Arsip -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Informasi Arsip
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Kode Klasifikasi</th>
                                <td><strong>{{ $arsip->kodeKlasifikasi->kode ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <th>Judul Arsip</th>
                                <td>{{ $arsip->uraian_arsip }}</td>
                            </tr>
                            <tr>
                                <th>Sub Bagian Pengaju</th>
                                <td>{{ $arsip->subBagian->nama_sub_bagian ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Tahun Arsip</th>
                                <td>{{ $arsip->tahun_arsip }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Arsip</th>
                                <td>{{ $arsip->tanggal_arsip ? \Carbon\Carbon::parse($arsip->tanggal_arsip)->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jumlah Berkas</th>
                                <td>{{ $arsip->jumlah_berkas }} {{ $arsip->satuan_arsip }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Status Arsip</th>
                                <td>
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
                                <td>{{ $arsip->aktif_sampai ? \Carbon\Carbon::parse($arsip->aktif_sampai)->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Inaktif Sampai</th>
                                <td>{{ $arsip->inaktif_sampai ? \Carbon\Carbon::parse($arsip->inaktif_sampai)->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Keterangan JRA</th>
                                <td>{{ $arsip->keterangan_jra ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Lokasi Asal (Sub Bagian)</th>
                                <td>
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
                                <td>
                                    Rak: <strong>{{ $arsip->rak ? $arsip->rak->nomor_rak : '-' }}</strong>, 
                                    Box: <strong>{{ $arsip->box ? $arsip->box->nomor_box : '-' }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Diajukan Pada</th>
                                <td>{{ $arsip->created_at ? $arsip->created_at->format('d/m/Y H:i') : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- File Dokumen Arsip -->
                @if($arsip->file_dokumen)
                <div class="mt-3">
                    <label class="form-label fw-semibold">File Dokumen Arsip</label>
                    <div>
                        <a href="{{ Storage::url('arsip/' . $arsip->file_dokumen) }}" 
                           target="_blank" 
                           class="btn btn-sm btn-info">
                            <i class="bi bi-download me-1"></i> Download Dokumen
                        </a>
                    </div>
                </div>
                @endif
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
                        
                        <div class="alert alert-warning mt-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Perhatian:</strong> Arsip ini tidak memiliki file berita acara. Disarankan untuk menolak pengajuan.
                        </div>
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
                    Verifikasi Lokasi Arsip
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.arsip-masuk.terima', $arsip->id) }}" 
                      method="POST"
                      id="formTerima">
                    @csrf
                    
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Info:</strong> Verifikasi ini hanya menyimpan usulan lokasi rak dan box.
                        Pemindahan arsip dilakukan pada tahap selanjutnya.
                    </div>
                    
                    <!-- Pilih Lokasi Tujuan -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lokasi Tujuan Arsip <span class="text-danger">*</span></label>
                        <select name="lokasi_tujuan" id="lokasi_tujuan" class="form-select" required>
                            <option value="">-- Pilih Lokasi --</option>
                            <option value="RECORD_CENTER_PERMANEN">Record Center Permanen</option>
                            <option value="RECORD_CENTER_INAKTIF">Record Center Inaktif</option>
                        </select>
                        @error('lokasi_tujuan')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Pilih Rak -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rak Baru <span class="text-danger">*</span></label>
                        <select name="rak_id_baru" id="rak_id_baru" class="form-select" required>
                            <option value="">-- Pilih Lokasi Terlebih Dahulu --</option>
                        </select>
                        @error('rak_id_baru')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Pilih Box -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Box Baru <span class="text-danger">*</span></label>
                        <select name="box_id_baru" id="box_id_baru" class="form-select" required>
                            <option value="">-- Pilih Rak Terlebih Dahulu --</option>
                        </select>
                        @error('box_id_baru')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan (Opsional)</label>
                        <textarea name="catatan" class="form-control" rows="3" 
                                placeholder="Tambahkan catatan verifikasi...">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Simpan Verifikasi Lokasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Informasi Verifikasi -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Status Verifikasi
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Diajukan oleh Sub Bagian</h6>
                            <p class="text-muted small mb-0">{{ $arsip->created_at ? $arsip->created_at->format('d/m/Y H:i') : '-' }}</p>
                            <p class="small">Lokasi: {{ $arsip->rak ? $arsip->rak->nomor_rak : '-' }}, Box {{ $arsip->box ? $arsip->box->nomor_box : '-' }}</p>
                        </div>
                    </div>
                    
                    @if($arsip->tanggal_diverifikasi)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Lokasi Arsip Diverifikasi Admin</h6>
                            <p class="text-muted small mb-0">
                                {{ $arsip->tanggal_diverifikasi ? \Carbon\Carbon::parse($arsip->tanggal_diverifikasi)->format('d/m/Y H:i') : '-' }}
                            </p>
                            @if($arsip->diverifikasi_oleh)
                            <p class="small">Oleh: {{ $arsip->verifikator->name ?? 'Admin' }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    @if($arsip->status_pindah == 'DIPINDAHKAN')
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
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
                
                @if($arsip->catatan_verifikasi)
                <div class="alert alert-light mt-3">
                    <h6><i class="bi bi-chat-text me-2"></i> Catatan Verifikasi Lokasi</h6>
                    <p class="mb-0">{{ $arsip->catatan_verifikasi }}</p>
                </div>
                @endif
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formTerima = document.getElementById('formTerima');
    const lokasiTujuan = document.getElementById('lokasi_tujuan');
    const rakSelect = document.getElementById('rak_id_baru');
    const boxSelect = document.getElementById('box_id_baru');
    
    // Data rak dan box dari server (dikirim dari controller)
    const rakData = @json($rakOptions ?? []);
    const boxData = @json($boxOptions ?? []);
    
    // Filter rak berdasarkan lokasi
    function filterRak() {
        const selectedLokasi = lokasiTujuan.value;
        rakSelect.innerHTML = '<option value="">-- Pilih Rak --</option>';
        boxSelect.innerHTML = '<option value="">-- Pilih Rak Terlebih Dahulu --</option>';
        
        if (!selectedLokasi) {
            rakSelect.innerHTML = '<option value="">-- Pilih Lokasi Terlebih Dahulu --</option>';
            return;
        }
        
        const filteredRak = rakData.filter(function(rak) {
            return rak.lokasi_arsip === selectedLokasi;
        });
        
        if (filteredRak.length === 0) {
            rakSelect.innerHTML = '<option value="">-- Tidak ada rak di lokasi ini --</option>';
        } else {
            filteredRak.forEach(function(rak) {
                const option = document.createElement('option');
                option.value = rak.id;
                option.textContent = rak.nomor_rak;
                rakSelect.appendChild(option);
            });
        }
    }
    
    // Filter box berdasarkan rak
    function filterBox() {
        const selectedRakId = rakSelect.value;
        boxSelect.innerHTML = '<option value="">-- Pilih Box --</option>';
        
        if (!selectedRakId) {
            boxSelect.innerHTML = '<option value="">-- Pilih Rak Terlebih Dahulu --</option>';
            return;
        }
        
        const filteredBox = boxData.filter(function(box) {
            return box.rak_id == selectedRakId;
        });
        
        if (filteredBox.length === 0) {
            boxSelect.innerHTML = '<option value="">-- Tidak ada box di rak ini --</option>';
        } else {
            filteredBox.forEach(function(box) {
                const option = document.createElement('option');
                option.value = box.id;
                option.textContent = box.nomor_box;
                boxSelect.appendChild(option);
            });
        }
    }
    
    // Event listeners
    lokasiTujuan.addEventListener('change', filterRak);
    rakSelect.addEventListener('change', filterBox);
    
    // Submit form
    formTerima.addEventListener('submit', function(e) {
        const rakId = rakSelect.value;
        const boxId = boxSelect.value;
        const lokasi = lokasiTujuan.value;
        
        if (!lokasi) {
            e.preventDefault();
            alert('Harap pilih lokasi tujuan terlebih dahulu.');
            return;
        }
        
        if (!rakId) {
            e.preventDefault();
            alert('Harap pilih rak tujuan.');
            return;
        }
        
        if (!boxId) {
            e.preventDefault();
            alert('Harap pilih box tujuan.');
            return;
        }
        
        // Konfirmasi
        const rakName = rakSelect.options[rakSelect.selectedIndex]?.text || '-';
        const boxName = boxSelect.options[boxSelect.selectedIndex]?.text || '-';
        const lokasiLabel = lokasiTujuan.options[lokasiTujuan.selectedIndex]?.text || '-';
        
        if (!confirm('Anda akan memverifikasi arsip ke lokasi:\n' +
                    'Lokasi: ' + lokasiLabel + '\n' +
                    'Rak: ' + rakName + '\n' +
                    'Box: ' + boxName + '\n\n' +
                    'Lanjutkan?')) {
            e.preventDefault();
        }
    });
});
</script>
@endsection