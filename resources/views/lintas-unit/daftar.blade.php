@extends('layouts.app')

@section('page-title', 'Daftar Arsip Unit')
@section('page-subtitle', 'Penelusuran Arsip Antar Unit')

@section('content')

<div class="mb-3">
    <a href="{{ url()->previous() }}" class="btn btn-light border back-btn">
        <i class="bi bi-arrow-left me-1"></i>
        Kembali
    </a>
</div>

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
                        <th width="160" class="text-center">Aksi</th>
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

                        </td>

                        <td>
                            <span class="tahun-badge">
                                {{ $arsip->tahun_arsip }}
                            </span>
                        </td>

                       <td>
                            <span class="plain-text">
                                {{ !empty($arsip->nomor_rak) ? $arsip->nomor_rak : '-' }}
                            </span>
                        </td>

                        <td>
                            <span class="plain-text">
                                {{ !empty($arsip->nomor_box) ? $arsip->nomor_box : '-' }}
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

                        <!-- <td class="text-center">

                            @if(!empty($arsip->file_dokumen))

                                <a href="{{ asset('storage/arsip/' . $arsip->file_dokumen) }}"
                                   target="_blank"
                                   class="pdf-btn">

                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                    <span>Lihat PDF</span>

                                </a>

                            @else

                                <span class="text-muted fw-semibold">-</span>

                            @endif

                        </td> -->

                        <td class="text-center">

    <div class="d-flex justify-content-center gap-2 flex-wrap">

        <a href="{{ route('arsip.show', $arsip->id) }}"
           class="btn btn-info btn-sm">
            <i class="bi bi-eye"></i> Detail
        </a>

      @php
    $url = $arsip->file_dokumen
        ? asset('storage/arsip/'.$arsip->file_dokumen)
        : $arsip->link_foto;
@endphp

@if($url)
    <a href="{{ $url }}" target="_blank" class="pdf-btn">
        <i class="bi bi-file-earmark-fill"></i>
        Lihat File
    </a>
@endif
    </div>

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
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid #eef2f7;
}

.card-header{
    background: linear-gradient(to right, #ffffff, #f8fbff);
}

.table thead th{
    background: #f8fafc;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
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
    transition: all .25s ease;
}

.table-hover tbody tr:hover{
    background: #f8fbff;
}

.kode-badge{
    background: #eef4ff;
    color: #2563eb;
    padding: 7px 13px;
    border-radius: 11px;
    font-weight: 700;
    font-size: 13px;
    display: inline-block;
}

.tahun-badge{
    background: #ecfeff;
    color: #0891b2;
    padding: 7px 13px;
    border-radius: 11px;
    font-weight: 700;
    font-size: 13px;
    display: inline-block;
}

.plain-text{
    font-weight: 600;
    color: #334155;
    font-size: 14px;
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
    border-radius: 14px !important;
    height: 46px;
    box-shadow: none;
    border-color: #e2e8f0;
}

.search-box .form-control:focus{
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.08);
}

.search-box .input-group-text{
    border-radius: 14px 0 0 14px;
    border-color: #e2e8f0;
}

.search-box .btn{
    border-radius: 14px;
    height: 46px;
    font-weight: 600;
}

.btn-primary{
    border-radius: 12px;
    font-weight: 600;
    padding: 8px 16px;
}

.back-btn{
    border-radius: 12px;
    padding: 9px 16px;
    font-weight: 600;
    transition: .2s ease;
}

.back-btn:hover{
    background: #f8fafc;
    transform: translateX(-2px);
}

.pdf-btn{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 9px 16px;
    border-radius: 12px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all .25s ease;
    box-shadow: 0 4px 12px rgba(220,38,38,.18);
}

.pdf-btn i{
    font-size: 15px;
}

.pdf-btn:hover{
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(220,38,38,.28);
    background: linear-gradient(135deg, #dc2626, #b91c1c);
}

.pdf-btn:active{
    transform: scale(.97);
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
    padding: 8px 14px;
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

    .pdf-btn{
        padding: 8px 12px;
        font-size: 12px;
    }

}

</style>

@endsection