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
       {{-- Status Pemindahan Arsip --}}
<div class="col-md-12 mt-4">
    <div class="card border shadow-sm">
        <div class="card-header bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                Status Pemindahan Arsip
            </h6>
        </div>

        <div class="card-body">
            <div class="row align-items-center">

                {{-- Status --}}
                <div class="col-md-4 mb-2">
                    <strong>Status Pemindahan:</strong><br>
                    @php
                        $pindahColors = [
                            'BELUM PINDAH' => 'secondary',
                            'DIAJUKAN' => 'warning',
                            'DIPINDAHKAN' => 'success',
                            'DITOLAK' => 'danger',
                            'LANGSUNG' => 'info'
                        ];
                        $pindahColor = $pindahColors[$arsip->status_pindah] ?? 'secondary';
                    @endphp

                    <span class="badge bg-{{ $pindahColor }} fs-6">
                        {{ $arsip->status_pindah ?? '-' }}
                    </span>
                </div>

                {{-- Tanggal --}}
                <div class="col-md-4 mb-2">
                    <strong>Tanggal Dipindahkan:</strong><br>
                    {{ $arsip->tanggal_dipindahkan
                        ? \Carbon\Carbon::parse($arsip->tanggal_dipindahkan)->format('d/m/Y H:i')
                        : '-' }}
                </div>

                {{-- Download BAP --}}
                <div class="col-md-4 mb-2 text-md-end">
                    <strong>Berita Acara:</strong><br>

                    @if(isset($beritaAcara) && $beritaAcara->file_bap)
                        <a href="{{ Storage::url('berita_acara/' . $beritaAcara->file_bap) }}"
                           target="_blank"
                           class="btn btn-sm btn-outline-primary mt-1">
                            <i class="bi bi-download"></i> Download BAP
                        </a>
                    @else
                        <span class="text-muted d-block mt-1">
                            Belum tersedia
                        </span>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
<!-- End Riwayat/History Arsip -->
        
        <div class="mt-4 pt-3 border-top">
            <div class="d-flex gap-2 align-items-center">

                <a href="{{ route('subbagian.riwayat-pemindahan.index') }}"
                class="btn btn-secondary">
                    ⬅ Kembali
                </a>

                <!-- <a href="{{ route('arsip.edit', $arsip->id) }}"
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
                </form> -->

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

