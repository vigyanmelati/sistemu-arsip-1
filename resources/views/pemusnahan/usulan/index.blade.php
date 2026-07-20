@extends('layouts.app')

@section('title', 'Pemusnahan Arsip')

@section('content')
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="container-fluid py-4">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="bi bi-trash3 text-danger me-2"></i>
                Pemusnahan Arsip
            </h4>
            <p class="text-muted mb-0">
                <i class="bi bi-calendar-check me-1"></i>
                Daftar kegiatan pemusnahan arsip per periode/tahun
            </p>
        </div>

        <a href="{{ route('pemusnahan.usulan.create') }}"
           class="btn btn-primary shadow-sm mt-2 mt-md-0">
            <i class="bi bi-plus-circle me-2"></i>
            Buat Pemusnahan Baru
        </a>
    </div>

    {{-- ================= INFO CARD ================= --}}
    <div class="alert alert-primary alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex">
            <div class="me-3">
                <i class="bi bi-info-circle-fill fs-4"></i>
            </div>
            <div>
                <strong class="d-block mb-1">Informasi Penting</strong>
                <ul class="mb-0 ps-3">
                    <li>Setiap pemusnahan merupakan <strong>satu kegiatan / batch</strong></li>
                    <li>Arsip tetap berstatus <strong>HABIS RETENSI</strong></li>
                    <li>Keputusan akhir dicatat pada dokumen pemusnahan</li>
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    {{-- ================= STATISTIK CARD ================= --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card bg-primary text-white shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Total Kegiatan</h6>
                            <h3 class="mb-0 fw-bold">{{ $pemusnahans->count() }}</h3>
                        </div>
                        <i class="bi bi-folder2-open fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card bg-success text-white shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Selesai</h6>
                            <h3 class="mb-0 fw-bold">
                                {{ $pemusnahans->where('status', 'dimusnahkan')->count() }}
                            </h3>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card bg-warning text-dark shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Proses</h6>
                            <h3 class="mb-0 fw-bold">
                                {{ $pemusnahans->whereNotIn('status', ['dimusnahkan', 'draft'])->count() }}
                            </h3>
                        </div>
                        <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Draft</h6>
                            <h3 class="mb-0 fw-bold">
                                {{ $pemusnahans->where('status', 'draft')->count() }}
                            </h3>
                        </div>
                        <i class="bi bi-pencil-square fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-table text-primary fs-5 me-2"></i>
                <h5 class="card-title mb-0 fw-semibold">Daftar Kegiatan Pemusnahan</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="10%" class="text-center">Tahun</th>
                            <th width="20%">Tanggal Usulan</th>
                            <th width="15%" class="text-center">Jumlah Arsip</th>
                            <th width="15%">Keterangan</th>
                            <th width="15%">Status</th>
                            <th width="20%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pemusnahans as $index => $item)
                        <tr>
                            <td class="text-center fw-semibold text-muted">{{ $index + 1 }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-secondary px-3 py-2 fw-normal" style="background-color: #f8f9fa !important;">
                                    <i class="bi bi-calendar me-1"></i>
                                    {{ $item->tahun }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                <i class="bi bi-calendar-date text-muted me-2"></i>
                                {{ \Carbon\Carbon::parse($item->tanggal_usulan)->translatedFormat('d F Y') }}
                            </td>
                            <td class="text-center">
                                <span class="px-3 py-2 fw-normal" style="background-color: #eef2ff; color: #4f46e5; border-radius: 6px; font-size: 0.875rem;">
                                    <i class="bi bi-archive me-1"></i>
                                    {{ $item->details_count }} Arsip
                                </span>
                            </td>
                            <td>
                                @if($item->keterangan)
                                    {{ $item->keterangan }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @switch($item->status)
                                    @case('draft')
                                        <span class="px-3 py-2 fw-normal" style="background-color: #fef3c7; color: #92400e; border-radius: 6px; font-size: 0.875rem;">
                                            <i class="bi bi-pencil me-1"></i> Draft
                                        </span>
                                        @break
                                    @case('diajukan_ke_anri')
                                        <span class="px-3 py-2 fw-normal" style="background-color: #dbeafe; color: #1e40af; border-radius: 6px; font-size: 0.875rem;">
                                            <i class="bi bi-send me-1"></i> Diajukan ke ANRI
                                        </span>
                                        @break
                                    @case('disetujui_anri')
                                        <span class="px-3 py-2 fw-normal" style="background-color: #cffafe; color: #0e7490; border-radius: 6px; font-size: 0.875rem;">
                                            <i class="bi bi-check-circle me-1"></i> Disetujui ANRI
                                        </span>
                                        @break
                                    @case('menunggu_persetujuan_kpu')
                                        <span class="px-3 py-2 fw-normal" style="background-color: #f1f5f9; color: #475569; border-radius: 6px; font-size: 0.875rem;">
                                            <i class="bi bi-clock-history me-1"></i> Menunggu Persetujuan KPU RI
                                        </span>
                                        @break
                                    @case('disetujui_kpu')
                                        <span class="px-3 py-2 fw-normal" style="background-color: #dcfce7; color: #166534; border-radius: 6px; font-size: 0.875rem;">
                                            <i class="bi bi-check-circle-fill me-1"></i> Disetujui KPU RI
                                        </span>
                                        @break
                                    @case('dimusnahkan')
                                        <span class="px-3 py-2 fw-normal" style="background-color: #f1f5f9; color: #334155; border-radius: 6px; font-size: 0.875rem;">
                                            <i class="bi bi-trash me-1"></i> Sudah Dimusnahkan
                                        </span>
                                        @break
                                    @default
                                        <span class="px-3 py-2 fw-normal" style="background-color: #f8f9fa; color: #6c757d; border-radius: 6px; font-size: 0.875rem;">-</span>
                                @endswitch
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                    <a href="{{ route('pemusnahan.usulan.show', $item->id) }}"
                                    class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>

                                    @if ($item->status === 'draft')
                                        <a href="{{ route('pemusnahan.usulan.edit', $item->id) }}"
                                        class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </a>

                                        <form action="{{ route('pemusnahan.usulan.destroy', $item->id) }}"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus pengajuan pemusnahan ini? Arsip yang sudah dimasukkan akan dikembalikan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash me-1"></i> Hapus
                                            </button>
                                        </form>

                                        <a href="{{ route('pemusnahan.sidang', $item->id) }}"
                                        class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-chat-dots me-1"></i> Penelitian/Penilaian
                                        </a>
                                    @endif

                                    @if ($item->status === 'diajukan_ke_anri')
                                        <a href="{{ route('pemusnahan.anri', $item->id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-building me-1"></i> ANRI
                                        </a>
                                    @endif

                                    @if (in_array($item->status, ['disetujui_anri', 'menunggu_persetujuan_kpu']))
                                        <a href="{{ route('pemusnahan.kpu', $item->id) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-check2-circle me-1"></i> KPU
                                        </a>
                                    @endif

                                    @if ($item->status === 'disetujui_kpu')
                                        <a href="{{ route('pemusnahan.eksekusi', $item->id) }}"
                                        class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-play-circle me-1"></i> Eksekusi
                                        </a>
                                    @endif

                                    @if ($item->status === 'dimusnahkan')
                                        <span class="badge bg-secondary bg-opacity-25 text-secondary px-3 py-2">
                                            <i class="bi bi-check-all me-1"></i> Selesai
                                        </span>
                                    @endif
                                </div>
                            </td>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                <h6 class="text-muted">Belum ada kegiatan pemusnahan</h6>
                                <small class="text-muted">Silakan buat pemusnahan baru untuk memulai</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
        transition: all 0.2s ease;
    }
    
    .btn-sm {
        transition: all 0.2s ease;
    }
    
    .btn-sm:hover {
        transform: translateY(-1px);
    }
    
    .card {
        transition: transform 0.2s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    /* Style soft untuk semua badge */
    /* .badge, span[style*="border-radius"] {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-weight: 500;
        letter-spacing: 0.3px;
    } */
    .badge, span[style*="border-radius"] {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-weight: 500;
    letter-spacing: 0.3px;
}
</style>
@endpush