{{-- resources/views/arsip/show.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Detail Arsip')
@section('page-subtitle', 'Informasi Lengkap Arsip')

@section('content')
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Detail Arsip</h6>
        {{-- <div class="btn-group">
            <a href="{{ route('arsip.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('arsip.edit', $arsip->id) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil"></i> Edit
            </a>
        </div> --}}
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Informasi Utama -->
            <div class="col-md-6">
                <h6 class="font-weight-bold text-primary mb-3">Informasi Utama</h6>
                <table class="table table-borderless">
                    <tr>
                        <td width="40%"><strong>Kode Klasifikasi</strong></td>
                        <td>: {{ $arsip->kodeKlasifikasi->kode ?? 'N/A' }} - {{ $arsip->kodeKlasifikasi->uraian ?? '' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Judul Arsip</strong></td>
                        <td>: {{ $arsip->uraian_arsip }}</td>
                    </tr>
                    <tr>
                        <td><strong>Sub Bagian</strong></td>
                        <td>: {{ $arsip->subBagian->nama_sub_bagian ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tahun Arsip</strong></td>
                        <td>: {{ $arsip->tahun_arsip }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Arsip</strong></td>
                        <td>: {{ $arsip->tanggal_arsip ? \Carbon\Carbon::parse($arsip->tanggal_arsip)->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status Arsip</strong></td>
                        <td>: 
                            @php
                                $statusColors = [
                                    'AKTIF' => 'success',
                                    'INAKTIF' => 'warning',
                                    'MUSNAH' => 'danger',
                                    'PERMANEN' => 'info'
                                ];
                                $color = $statusColors[$arsip->status_arsip] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ $arsip->status_arsip }}</span>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Informasi Fisik -->
            <div class="col-md-6">
                <h6 class="font-weight-bold text-primary mb-3">Informasi Fisik</h6>
                <table class="table table-borderless">
                    <tr>
                        <td width="40%"><strong>Jumlah Berkas</strong></td>
                        <td>: {{ $arsip->jumlah_berkas ?? '1' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Satuan Arsip</strong></td>
                        <td>: {{ $arsip->satuan_arsip ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tingkat Perkembangan</strong></td>
                        <td>: {{ $arsip->tingkat_perkembangan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Nomor Rak</strong></td>
                        <td>: {{ $arsip->nomor_rak ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Nomor Box</strong></td>
                        <td>: {{ $arsip->nomor_box ?: '-' }}</td>
                    </tr>
                     <tr>
                        <td><strong>Nomor Sampul</strong></td>
                        <td>: {{ $arsip->nomor_sampul ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Kondisi Fisik</strong></td>
                        <td>: 
                            @php
                                $kondisiColors = [
                                    'BAIK' => 'success',
                                    'RUSAK' => 'warning',
                                    'HILANG' => 'danger'
                                ];
                                $kondisiColor = $kondisiColors[$arsip->keterangan] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $kondisiColor }}">
                                {{ $arsip->keterangan ?: '-' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Media Arsip</strong></td>
                        <td>: 
                            @php
                                $mediaColors = [
                                    'TEKSTUAL' => 'primary',
                                    'DIGITAL' => 'info',
                                ];
                                $mediaColor = $mediaColors[$arsip->media_arsip] ?? 'secondary';
                            @endphp

                            @if($arsip->media_arsip)
                                <span class="badge bg-{{ $mediaColor }}">
                                    {{ $arsip->media_arsip }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td><strong>File Dokumen</strong></td>
                        <td>: 
                            @if($arsip->file_dokumen)
                                <div>
                                    <a href="{{ Storage::url('arsip/' . $arsip->file_dokumen) }}" 
                                    target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Lihat File
                                    </a>
                                    <small class="text-muted d-block mt-1">
                                        {{ $arsip->file_dokumen }}
                                    </small>
                                </div>
                            @else
                                <span class="text-muted">Tidak ada file</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Informasi Retensi -->
            <div class="col-md-6">
                <h6 class="font-weight-bold text-primary mb-3">Informasi Retensi</h6>
                <table class="table table-borderless">
                    <tr>
                        <td width="40%"><strong>Masa Retensi Aktif</strong></td>
                        <td>: {{ $arsip->aktif_tahun ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Masa Retensi Inaktif</strong></td>
                        <td>: {{ $arsip->inaktif_tahun ?: '-' }}</td>
                    </tr>
                    @if($arsip->tanggal_referensi)
                    <tr>
                        <td><strong>Tanggal Referensi</strong></td>
                        <td>: {{ $arsip->tanggal_referensi ? \Carbon\Carbon::parse($arsip->tanggal_referensi)->format('d/m/Y') : '-' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Aktif Sampai</strong></td>
                        <td>: 
                            @if($arsip->aktif_sampai)
                                {{ \Carbon\Carbon::parse($arsip->aktif_sampai)->format('d/m/Y') }}
                                @php
                                    $aktifDate = \Carbon\Carbon::parse($arsip->aktif_sampai);
                                    $now = \Carbon\Carbon::now();
                                    if($now > $aktifDate) {
                                        echo '<span class="badge bg-danger ms-2">Lewat</span>';
                                    } else {
                                        echo '<span class="badge bg-success ms-2">Aktif</span>';
                                    }
                                @endphp
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Inaktif Sampai</strong></td>
                        <td>: 
                            @if($arsip->inaktif_sampai)
                                {{ \Carbon\Carbon::parse($arsip->inaktif_sampai)->format('d/m/Y') }}
                                @php
                                    $inaktifDate = \Carbon\Carbon::parse($arsip->inaktif_sampai);
                                    $now = \Carbon\Carbon::now();
                                    if($now > $inaktifDate) {
                                        echo '<span class="badge bg-danger ms-2">Lewat</span>';
                                    } else {
                                        echo '<span class="badge bg-warning ms-2">Akan Datang</span>';
                                    }
                                @endphp
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Keterangan JRA</strong></td>
                        <td>: 
                            @php
                                $jraColors = [
                                    'PERMANEN' => 'info',
                                    'MUSNAH' => 'danger'
                                ];
                                $jraColor = $jraColors[$arsip->keterangan_jra] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $jraColor }}">{{ $arsip->keterangan_jra ?: '-' }}</span>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Informasi Sistem -->
            <div class="col-md-6">
                <h6 class="font-weight-bold text-primary mb-3">Informasi Sistem</h6>
                <table class="table table-borderless">
                    <tr>
                        <td width="40%"><strong>Tanggal Masuk</strong></td>
                        <td>: {{ $arsip->tanggal_masuk ? \Carbon\Carbon::parse($arsip->tanggal_masuk)->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Dibuat Oleh</strong></td>
                        <td>: {{ $arsip->created_by ? \App\Models\User::find($arsip->created_by)->name ?? 'System' : 'System' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Dibuat</strong></td>
                        <td>: {{ $arsip->created_at ? \Carbon\Carbon::parse($arsip->created_at)->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Terakhir Diupdate</strong></td>
                        <td>: {{ $arsip->updated_at ? \Carbon\Carbon::parse($arsip->updated_at)->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Catatan Tambahan -->
            @if($arsip->aktif_tahun && stripos($arsip->aktif_tahun, 'SETELAH') !== false && !$arsip->tanggal_referensi)
            <div class="col-md-12 mt-3">
                <div class="alert alert-warning">
                    <strong>Catatan:</strong> Arsip ini menggunakan format "SETELAH" untuk masa retensi tetapi tanggal referensi belum diisi. Status arsip otomatis di-set ke <strong>{{ $arsip->status_arsip }}</strong>.
                </div>
            </div>
            @endif
        </div>
        <!-- Riwayat/History Arsip -->
<!-- Riwayat/History Arsip -->
<div class="col-md-12 mt-4">
    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Perjalanan Arsip</h6>
        </div>
        <div class="card-body">
            
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs" id="riwayatTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pindah-tab" data-bs-toggle="tab" 
                            data-bs-target="#pindah" type="button" role="tab">
                        <i class="bi bi-box-arrow-in-right"></i> Riwayat Perpindahan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pengajuan-tab" data-bs-toggle="tab" 
                            data-bs-target="#pengajuan" type="button" role="tab">
                        <i class="bi bi-file-earmark-text"></i> Riwayat Pengajuan
                    </button>
                </li>
            </ul>
            
            <!-- Tab Content -->
            <div class="tab-content mt-3" id="riwayatTabContent">
                
                <!-- Tab 1: Riwayat Perpindahan -->
                <div class="tab-pane fade show active" id="pindah" role="tabpanel">
                    @if($riwayatPindah->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th width="15%">Tanggal</th>
                                        <th width="20%">Lokasi Asal</th>
                                        <th width="20%">Lokasi Tujuan</th>
                                        <th width="25%">Alasan/Keterangan</th>
                                        <!-- <th width="20%">Oleh</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($riwayatPindah as $rp)
                                    <tr>
                                        <td>
                                            {{ \Carbon\Carbon::parse($rp->tanggal_pindah)->format('d/m/Y') }}
                                            <br>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($rp->created_at)->format('H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($rp->dari_rak || $rp->dari_box)
                                                Rak: <strong>{{ $rp->dari_rak ?? '-' }}</strong><br>
                                                Box: <strong>{{ $rp->dari_box ?? '-' }}</strong>
                                            @else
                                                <span class="text-muted">Lokasi awal</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($rp->ke_rak || $rp->ke_box)
                                                Rak: <strong>{{ $rp->ke_rak ?? '-' }}</strong><br>
                                                Box: <strong>{{ $rp->ke_box ?? '-' }}</strong>
                                            @else
                                                <span class="text-muted">Belum dipindah</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $rp->alasan_pindah ?? $rp->keterangan }}
                                            @if($rp->alasan_pindah == 'Arsip diterima dari Sub Bagian ke Unit Kearsipan')
                                                <br>
                                                <small class="text-success">
                                                    <i class="bi bi-check-circle"></i> Dipindah ke Unit Kearsipan
                                                </small>
                                            @endif
                                        </td>
                                        <!-- <td>
                                            {{ $rp->user->name ?? 'System' }}
                                            <br>
                                            <small class="text-muted">
                                                {{ $rp->user->role ?? '' }}
                                            </small>
                                        </td> -->
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Belum ada riwayat perpindahan untuk arsip ini.
                        </div>
                    @endif
                </div>
                
                <!-- Tab 2: Riwayat Pengajuan -->
                <div class="tab-pane fade" id="pengajuan" role="tabpanel">
                    @if($beritaAcaraDetail->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th width="15%">Nomor BAP</th>
                                        <th width="15%">Tanggal</th>
                                        <th width="20%">Sub Bagian Pengaju</th>
                                        <th width="15%">Status BAP</th>
                                        <th width="20%">Status Arsip</th>
                                        <!-- <th width="15%">Dibuat Oleh</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($beritaAcaraDetail as $bad)
                                    <tr>
                                        <td>
                                            <strong>{{ $bad->beritaAcara->nomor_bap ?? '-' }}</strong>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($bad->beritaAcara->tanggal_bap)->format('d/m/Y') }}
                                        </td>
                                        <td>
                                            {{ $bad->beritaAcara->subBagian->nama_sub_bagian ?? '-' }}
                                        </td>
                                        <td>
                                            @php
                                                $bapStatusColors = [
                                                    'DIAJUKAN' => 'warning',
                                                    'DISETUJUI' => 'success',
                                                    'DITOLAK' => 'danger'
                                                ];
                                                $bapColor = $bapStatusColors[$bad->beritaAcara->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $bapColor }}">
                                                {{ $bad->beritaAcara->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $detailStatusColors = [
                                                    'DIAJUKAN' => 'warning',
                                                    'DIPINDAHKAN' => 'success'
                                                ];
                                                $detailColor = $detailStatusColors[$bad->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $detailColor }}">
                                                {{ $bad->status }}
                                            </span>
                                            @if($bad->ke_rak && $bad->ke_box)
                                                <br>
                                                <small class="text-muted">
                                                    Rak: {{ $bad->ke_rak }}, Box: {{ $bad->ke_box }}
                                                </small>
                                            @endif
                                        </td>
                                        <!-- <td>
                                            {{ $bad->beritaAcara->createdBy->name ?? '-' }}
                                        </td> -->
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Belum ada riwayat pengajuan Berita Acara Pemindahan (BAP) untuk arsip ini.
                        </div>
                    @endif
                    
                    <!-- Ringkasan Status Pengajuan -->
                    @if($arsip->status_pindah != 'BELUM')
                        <div class="mt-3 p-3 border rounded bg-light">
                            <h6 class="font-weight-bold">Status Pengajuan Terkini</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Status Pemindahan:</strong>
                                    @php
                                        $pindahColors = [
                                            'BELUM' => 'secondary',
                                            'DIAJUKAN' => 'warning',
                                            'DIPINDAHKAN' => 'success',
                                            'DITOLAK' => 'danger',
                                            'LANGSUNG' => 'info'
                                        ];
                                        $pindahColor = $pindahColors[$arsip->status_pindah] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $pindahColor }}">
                                        {{ $arsip->status_pindah }}
                                    </span>
                                </div>
                                @if($arsip->tanggal_dipindahkan)
                                <div class="col-md-6">
                                    <strong>Tanggal Dipindahkan:</strong>
                                    {{ \Carbon\Carbon::parse($arsip->tanggal_dipindahkan)->format('d/m/Y H:i') }}
                                </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                
            </div> <!-- End Tab Content -->
            
        </div>
    </div>
</div>
<!-- End Riwayat/History Arsip -->
        
        <div class="mt-4 pt-3 border-top">
            <div class="d-flex gap-2 align-items-center">

                <a href="{{ $returnUrl ?? route('arsip.index') }}"
                class="btn btn-secondary">
                    ⬅ Kembali
                </a>

                <a href="{{ route('arsip.edit', $arsip->id) }}"
                class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit Arsip
                </a>

                <form action="{{ route('arsip.destroy', $arsip->id) }}"
                    method="POST"
                    class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="btn btn-danger"
                            onclick="return confirm('Apakah Anda yakin ingin menghapus arsip ini?')">
                        <i class="bi bi-trash"></i> Hapus Arsip
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>


<style> 
    /* Warna dasar tab */
#riwayatTab .nav-link {
    color: #495057;
    background-color: #f1f3f5;
    border: 1px solid #dee2e6;
    margin-right: 5px;
    border-radius: 6px 6px 0 0;
    transition: all .2s ease;
}

/* Hover */
#riwayatTab .nav-link:hover {
    background-color: #e9ecef;
}

/* Tab aktif */
#riwayatTab .nav-link.active {
    background-color: #0d6efd; /* biru bootstrap */
    color: #fff;
    border-color: #0d6efd #0d6efd #fff;
    font-weight: 600;
}

</style>
@endsection

