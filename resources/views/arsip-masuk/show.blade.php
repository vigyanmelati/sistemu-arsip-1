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
                                <td>{{ $arsip->tanggal_arsip->format('d/m/Y') }}</td>
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
                                            'USUL_MUSNAH' => 'secondary'
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
                                <td>{{ $arsip->keterangan_jra }}</td>
                            </tr>
                            <tr>
                                <th>Lokasi Asal (Sub Bagian)</th>
                                <td>
                                    Rak: <strong>{{ $arsip->nomor_rak ?? '-' }}</strong>, 
                                    Box: <strong>{{ $arsip->nomor_box ?? '-' }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Diajukan Pada</th>
                                <td>{{ $arsip->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- History Perpindahan (jika ada) -->
                @if($arsip->historyPindah && $arsip->historyPindah->count() > 0)
                <div class="mt-4">
                    <label class="form-label fw-semibold">History Perpindahan Sebelumnya</label>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Dari</th>
                                    <th>Ke</th>
                                    <th>Alasan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($arsip->historyPindah->take(3) as $history)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($history->tanggal_pindah)->format('d/m/Y') }}</td>
                                    <td>
                                        @if($history->dari_rak)
                                            Rak: {{ $history->dari_rak }}, Box: {{ $history->dari_box }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($history->ke_rak)
                                            Rak: {{ $history->ke_rak }}, Box: {{ $history->ke_box }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($history->alasan_pindah, 50) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($arsip->historyPindah->count() > 3)
                        <div class="text-end">
                            <a href="{{ route('arsip.history', $arsip->id) }}" class="btn btn-sm btn-outline-primary">
                                Lihat Semua History
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
                
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
                    <!-- <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Berita Acara Pengajuan</h5>
                    </div> -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nomor BAP:</strong> {{ $bap->nomor_bap }}</p>
                                <p><strong>Tanggal BAP:</strong> {{ \Carbon\Carbon::parse($bap->tanggal_bap)->format('d-m-Y') }}</p>
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
                    Verifikasi Arsip
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.arsip-masuk.terima', $arsip->id) }}" method="POST" 
                    onsubmit="return confirmTerima()" id="formTerima">
                    @csrf
                    
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Info:</strong> Arsip akan dipindahkan dari lokasi Sub Bagian ke Unit Kearsipan
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Nomor Rak Baru <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_rak_baru" class="form-control" 
                                placeholder="Contoh: A-01" required
                                value="{{ old('nomor_rak_baru') }}">
                            @error('nomor_rak_baru')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Nomor Box Baru <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_box_baru" class="form-control" 
                                placeholder="Contoh: B-01" required
                                value="{{ old('nomor_box_baru') }}">
                            @error('nomor_box_baru')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
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
                            <i class="bi bi-check-circle me-2"></i>Verifikasi
                        </button>
                    </div>
                </form>
                
                <hr>
                
               
                <!-- <form action="{{ route('admin.arsip-masuk.tolak', $arsip->id) }}" method="POST" id="formTolak">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="alasan" class="form-control" rows="3" 
                                placeholder="Sebutkan alasan penolakan..." required>{{ old('alasan') }}</textarea>
                        @error('alasan')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger btn-lg" onclick="return confirm('Yakin menolak pengajuan ini?')">
                            <i class="bi bi-x-circle me-2"></i> Tolak Pengajuan
                        </button>
                    </div>
                </form> -->
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
                            <p class="text-muted small mb-0">{{ $arsip->created_at->format('d/m/Y H:i') }}</p>
                            <p class="small">Lokasi: Rak {{ $arsip->nomor_rak ?? '-' }}, Box {{ $arsip->nomor_box ?? '-' }}</p>
                        </div>
                    </div>
                    
                    @if($arsip->status_pindah == 'DITERIMA' || $arsip->status_pindah == 'DITOLAK')
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Diverifikasi oleh Admin</h6>
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
                            @if($arsip->nomor_rak && $arsip->nomor_box)
                            <p class="small">Lokasi Baru: Rak {{ $arsip->nomor_rak }}, Box {{ $arsip->nomor_box }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                
                @if($arsip->catatan_verifikasi)
                <div class="alert alert-light mt-3">
                    <h6><i class="bi bi-chat-text me-2"></i> Catatan Verifikasi</h6>
                    <p class="mb-0">{{ $arsip->catatan_verifikasi }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmAction">Ya, Lanjutkan</button>
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
    const formTolak = document.getElementById('formTolak');
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const confirmMessage = document.getElementById('confirmMessage');
    const confirmAction = document.getElementById('confirmAction');
    
    let currentForm = null;
    let formData = {};
    
    console.log('Script loaded'); // Debugging
    
    // Terima arsip
    formTerima.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form terima submitted'); // Debugging
        
        // Validasi file berita acara
        @if(!$arsip->file_berita_acara)
        if (!confirm('Arsip ini tidak memiliki file berita acara. Yakin ingin menerima?')) {
            console.log('User cancelled due to missing BA file');
            return;
        }
        @endif
        
        const nomorRakBaru = this.querySelector('[name="nomor_rak_baru"]').value;
        const nomorBoxBaru = this.querySelector('[name="nomor_box_baru"]').value;
        const catatan = this.querySelector('[name="catatan"]').value;
        
        console.log('Nomor Rak Baru:', nomorRakBaru); // Debugging
        console.log('Nomor Box Baru:', nomorBoxBaru); // Debugging
        
        if (!nomorRakBaru || !nomorBoxBaru) {
            alert('Harap isi nomor rak baru dan nomor box baru.');
            return;
        }
        
        // Simpan data form
        formData = {
            _token: this.querySelector('[name="_token"]').value,
            nomor_rak_baru: nomorRakBaru,
            nomor_box_baru: nomorBoxBaru,
            catatan: catatan
        };
        
        // Tampilkan konfirmasi
        confirmMessage.innerHTML = `
            <p><strong>Anda akan menerima pengajuan arsip ini:</strong></p>
            <div class="alert alert-info">
                <strong>Detail Perpindahan:</strong><br>
                • Dari Lokasi: Rak <strong>${'{{ $arsip->nomor_rak ?? "-" }}'}</strong>, Box <strong>${'{{ $arsip->nomor_box ?? "-" }}'}</strong><br>
                • Ke Lokasi: Rak <strong>${nomorRakBaru}</strong>, Box <strong>${nomorBoxBaru}</strong>
            </div>
            ${catatan ? `<div class="alert alert-light">
                <strong>Catatan:</strong><br>${catatan}
            </div>` : ''}
            <p><strong>Apakah Anda yakin?</strong></p>
            <small class="text-muted">Catatan perpindahan akan tersimpan di history.</small>
        `;
        
        currentForm = this;
        confirmAction.textContent = 'Ya, Terima';
        confirmModal.show();
    });
    
    // Tolak arsip
    formTolak.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form tolak submitted'); // Debugging
        
        const alasan = this.querySelector('[name="alasan"]').value;
        
        if (!alasan) {
            alert('Harap isi alasan penolakan.');
            return;
        }
        
        // Simpan data form
        formData = {
            _token: this.querySelector('[name="_token"]').value,
            alasan: alasan
        };
        
        confirmMessage.innerHTML = `
            <p><strong>Anda akan menolak pengajuan arsip ini:</strong></p>
            <div class="alert alert-warning">
                <strong>Alasan Penolakan:</strong><br>
                ${alasan}
            </div>
            <p><strong>Apakah Anda yakin?</strong></p>
            <small class="text-muted">Arsip akan dikembalikan ke Sub Bagian dengan status DITOLAK.</small>
        `;
        
        currentForm = this;
        confirmAction.textContent = 'Ya, Tolak';
        confirmModal.show();
    });
    
    // Handle konfirmasi modal
    // confirmAction.addEventListener('click', function() {
    //     console.log('Confirm button clicked'); // Debugging
        
    //     if (currentForm) {
    //         // Tutup modal
    //         confirmModal.hide();
            
    //         // Tampilkan loading
    //         const submitButton = currentForm.querySelector('button[type="submit"]');
    //         const originalText = submitButton.innerHTML;
    //         submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Memproses...';
    //         submitButton.disabled = true;
            
    //         // Kirim form menggunakan AJAX untuk debugging
    //         const formUrl = currentForm.action;
    //         console.log('Submitting to:', formUrl); // Debugging
    //         console.log('Form data:', formData); // Debugging
            
    //         fetch(formUrl, {
    //             method: 'POST',
    //             headers: {
    //                 'Content-Type': 'application/json',
    //                 'X-CSRF-TOKEN': formData._token,
    //                 'Accept': 'application/json'
    //             },
    //             body: JSON.stringify(formData)
    //         })
    //         .then(response => {
    //             console.log('Response status:', response.status); // Debugging
    //             return response.json();
    //         })
    //         .then(data => {
    //             console.log('Response data:', data); // Debugging
                
    //             if (data.success) {
    //                 // Redirect ke halaman index dengan pesan sukses
    //                 window.location.href = "{{ route('arsip-masuk.index') }}";
    //             } else {
    //                 // Tampilkan error
    //                 alert('Terjadi kesalahan: ' + (data.message || 'Tidak diketahui'));
    //                 submitButton.innerHTML = originalText;
    //                 submitButton.disabled = false;
    //             }
    //         })
    //         .catch(error => {
    //             console.error('Error:', error);
    //             alert('Terjadi kesalahan: ' + error.message);
    //             submitButton.innerHTML = originalText;
    //             submitButton.disabled = false;
    //         });
            
    //         // Atau langsung submit form (non-AJAX)
    //         // currentForm.submit();
    //     }
    // });

    // Ganti bagian JavaScript yang mengirim data
confirmAction.addEventListener('click', function() {
    console.log('Confirm button clicked');
    
    if (currentForm) {
        // Tutup modal
        confirmModal.hide();
        
        // Tampilkan loading
        const submitButton = currentForm.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Memproses...';
        submitButton.disabled = true;
        
        // Buat FormData
        const formData = new FormData(currentForm);
        
        console.log('FormData entries:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        // Kirim form secara tradisional (non-AJAX)
        // Ini lebih reliable untuk form dengan file upload
        currentForm.submit();
    }
});
    
    // Tambahkan event listener untuk bootstrap modal hidden
    document.getElementById('confirmModal').addEventListener('hidden.bs.modal', function () {
        console.log('Modal hidden'); // Debugging
        currentForm = null;
        formData = {};
    });
});
</script>
@endsection