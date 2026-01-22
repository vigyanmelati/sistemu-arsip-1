{{-- resources/views/arsip/show.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Detail Arsip')
@section('page-subtitle', 'Informasi Lengkap Arsip')

@section('content')
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Detail Arsip</h6>
        <div class="btn-group">
            <a href="{{ route('arsip.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('arsip.edit', $arsip->id) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil"></i> Edit
            </a>
        </div>
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
                        <td>: {{ $arsip->subBagian->nama ?? 'N/A' }}</td>
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
                        <td>: {{ $arsip->nomor_rak }}</td>
                    </tr>
                    <tr>
                        <td><strong>Nomor Box</strong></td>
                        <td>: {{ $arsip->nomor_box }}</td>
                    </tr>
                    <tr>
                        <td><strong>File Dokumen</strong></td>
                        <td>: 
                            @if($arsip->file_dokumen)
                                <a href="#" class="text-primary">{{ $arsip->file_dokumen }}</a>
                            @else
                                -
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
                        <td width="40%"><strong>Aktif (Tahun)</strong></td>
                        <td>: {{ $arsip->aktif_tahun }} tahun</td>
                    </tr>
                    <tr>
                        <td><strong>Inaktif (Tahun)</strong></td>
                        <td>: {{ $arsip->inaktif_tahun }} tahun</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Masuk</strong></td>
                        <td>: {{ $arsip->tanggal_masuk ? \Carbon\Carbon::parse($arsip->tanggal_masuk)->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Aktif Sampai</strong></td>
                        <td>: {{ $arsip->aktif_sampai ? \Carbon\Carbon::parse($arsip->aktif_sampai)->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Inaktif Sampai</strong></td>
                        <td>: {{ $arsip->inaktif_sampai ? \Carbon\Carbon::parse($arsip->inaktif_sampai)->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Keterangan JRA</strong></td>
                        <td>: {{ $arsip->keterangan_jra ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Informasi Sistem -->
            <div class="col-md-6">
                <h6 class="font-weight-bold text-primary mb-3">Informasi Sistem</h6>
                <table class="table table-borderless">
                    <tr>
                        <td width="40%"><strong>Dibuat Oleh</strong></td>
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
            
            <!-- Keterangan -->
            @if($arsip->keterangan_jra)
            <div class="col-md-12">
                <h6 class="font-weight-bold text-primary mb-3">Keterangan</h6>
                <div class="alert alert-info">
                    {{ $arsip->keterangan_jra }}
                </div>
            </div>
            @endif
        </div>
        
        <div class="mt-4 pt-3 border-top">
            <div class="btn-group">
                <a href="{{ route('arsip.edit', $arsip->id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit Arsip
                </a>
                <form action="{{ route('arsip.destroy', $arsip->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" 
                            onclick="return confirm('Apakah Anda yakin ingin menghapus arsip ini?')">
                        <i class="bi bi-trash"></i> Hapus Arsip
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection