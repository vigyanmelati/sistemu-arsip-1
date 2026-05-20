@extends('layouts.app')

@section('page-title', 'Daftar Arsip Unit')
@section('page-subtitle', 'Penelusuran Arsip Antar Unit')

@section('content')

<div class="card border-0 shadow-sm main-card">

    <div class="card-header bg-white border-0 py-4 px-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>
                <h4 class="mb-1 fw-bold text-dark">
                    <i class="bi bi-folder2-open text-primary me-2"></i>
                    {{ $title }}
                </h4>

                <small class="text-muted">
                    Menampilkan daftar arsip berdasarkan unit kerja
                </small>
            </div>

            <form method="GET" class="search-box d-flex gap-2">

                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>

                    <input type="text"
                           name="search"
                           class="form-control border-start-0"
                           placeholder="Cari judul arsip..."
                           value="{{ request('search') }}">
                </div>

                <button class="btn btn-primary px-4">
                    Cari
                </button>

            </form>

        </div>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead>
                    <tr>
                        <th width="60">No</th>
                        <th>Kode</th>
                        <th>Judul Arsip</th>
                        <th>Tahun</th>
                        <th>Rak</th>
                        <th>Box</th>
                        <th>Lokasi Arsip</th>
                        <th width="140" class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($arsips as $arsip)

                    <tr>

                        <td class="fw-semibold text-muted">
                            {{ $loop->iteration + ($arsips->currentPage() - 1) * $arsips->perPage() }}
                        </td>

                        <td>
                            <span class="kode-badge">
                                {{ $arsip->kodeKlasifikasi->kode ?? '-' }}
                            </span>
                        </td>

                        <td>

                            <div class="fw-semibold text-dark mb-1">
                                {{ Str::limit($arsip->uraian_arsip, 120) }}
                            </div>

                            <!-- <small class="text-muted">
                                Arsip digital unit kerja
                            </small> -->

                        </td>

                        <td>
                            <span class="tahun-badge">
                                {{ $arsip->tahun_arsip }}
                            </span>
                        </td>

                        <td>
                            <span class="soft-badge">
                                {{ $arsip->nomor_rak ?? '-' }}
                            </span>
                        </td>

                        <td>
                            <span class="soft-badge">
                                {{ $arsip->nomor_box ?? '-' }}
                            </span>
                        </td>

                        <td>

                            @php
                                $lokasi = [
                                    'RECORD_CENTER_PERMANEN' => 'Record Center Permanen',
                                    'RECORD_CENTER_INAKTIF' => 'Record Center Inaktif',
                                ];
                            @endphp

                            <span class="lokasi-badge">
                                <i class="bi bi-geo-alt-fill me-1"></i>
                                {{ $lokasi[$arsip->lokasi_arsip] ?? '-' }}
                            </span>

                        </td>

                        <td class="text-center">

                            @if(!empty($arsip->file_dokumen))

                                <a href="{{ asset('storage/' . $arsip->file_dokumen) }}"
   target="_blank"
   class="btn btn-sm btn-primary">

    <i class="bi bi-file-earmark-pdf-fill me-1"></i>
    Lihat PDF

</a>

                            @else

                                <span class="text-muted fw-semibold">-</span>

                            @endif

                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="8" class="text-center py-5">

                            <div class="empty-state">

                                <i class="bi bi-folder-x"></i>

                                <h6 class="mt-3 mb-1">
                                    Data arsip belum tersedia
                                </h6>

                                <small class="text-muted">
                                    Belum ada arsip yang dapat ditampilkan
                                </small>

                            </div>

                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <div class="card-footer bg-white border-0 py-3 px-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div class="text-muted small">
                Menampilkan
                <strong>{{ $arsips->firstItem() ?? 0 }}</strong>
                -
                <strong>{{ $arsips->lastItem() ?? 0 }}</strong>
                dari
                <strong>{{ $arsips->total() }}</strong>
                arsip
            </div>

            <div>
                {{ $arsips->withQueryString()->links('pagination::bootstrap-5') }}
            </div>

        </div>

    </div>

</div>

<style>

.main-card{
    border-radius: 22px;
    overflow: hidden;
}

.table thead th{
    background: #f8fafc;
    color: #64748b;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    border-bottom: 1px solid #eef2f7;
    padding: 18px 16px;
    white-space: nowrap;
}

.table td{
    padding: 18px 16px;
    border-color: #f1f5f9;
    vertical-align: middle;
}

.table-hover tbody tr{
    transition: all .2s ease;
}

.table-hover tbody tr:hover{
    background: #f8fbff;
}

.kode-badge{
    background: #eef4ff;
    color: #2563eb;
    padding: 7px 12px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 13px;
    display: inline-block;
}

.soft-badge{
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 7px 12px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    display: inline-block;
}

.tahun-badge{
    background: #ecfeff;
    color: #0891b2;
    padding: 7px 12px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 13px;
    display: inline-block;
}

.lokasi-badge{
    background: #eff6ff;
    color: #2563eb;
    padding: 8px 13px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
}

.search-box .form-control{
    min-width: 260px;
    border-radius: 12px !important;
    height: 46px;
    box-shadow: none;
}

.search-box .input-group-text{
    border-radius: 12px 0 0 12px;
}

.search-box .btn{
    border-radius: 12px;
    height: 46px;
    font-weight: 600;
}

.btn-primary{
    border-radius: 10px;
    font-weight: 600;
}

.empty-state i{
    font-size: 60px;
    color: #cbd5e1;
}

.pagination{
    margin-bottom: 0;
}

.page-link{
    border-radius: 10px !important;
    margin: 0 2px;
    border: none;
    color: #475569;
}

.page-item.active .page-link{
    background: #2563eb;
}

@media(max-width:768px){

    .search-box{
        width: 100%;
    }

    .search-box .input-group{
        width: 100%;
    }

    .search-box .form-control{
        min-width: 100%;
    }

}

</style>

@endsection