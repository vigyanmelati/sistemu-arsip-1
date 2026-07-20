@extends('layouts.app')

@section('title', 'Detail Pemusnahan Arsip')

@section('content')
<div class="container-fluid py-4">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="bi bi-folder2-open text-danger me-2"></i>
                Detail Pemusnahan Arsip &ndash; Tahun {{ $pemusnahan->tahun }}
            </h4>
            <p class="text-muted mb-0">
                Status:
                @php
                    $statusMap = [
                        'draft' => ['label' => 'Draft', 'bg' => '#fef3c7', 'color' => '#92400e', 'icon' => 'bi-pencil'],
                        'diajukan_ke_anri' => ['label' => 'Diajukan ke ANRI', 'bg' => '#dbeafe', 'color' => '#1e40af', 'icon' => 'bi-send'],
                        'disetujui_anri' => ['label' => 'Disetujui ANRI', 'bg' => '#cffafe', 'color' => '#0e7490', 'icon' => 'bi-check-circle'],
                        'menunggu_persetujuan_kpu' => ['label' => 'Menunggu Persetujuan KPU RI', 'bg' => '#f1f5f9', 'color' => '#475569', 'icon' => 'bi-clock-history'],
                        'disetujui_kpu' => ['label' => 'Disetujui KPU RI', 'bg' => '#dcfce7', 'color' => '#166534', 'icon' => 'bi-check-circle-fill'],
                        'dimusnahkan' => ['label' => 'Sudah Dimusnahkan', 'bg' => '#f1f5f9', 'color' => '#334155', 'icon' => 'bi-trash'],
                    ];
                    $s = $statusMap[$pemusnahan->status] ?? ['label' => '-', 'bg' => '#f8f9fa', 'color' => '#6c757d', 'icon' => 'bi-dash-circle'];
                @endphp
                <span class="px-3 py-2 fw-normal" style="background-color: {{ $s['bg'] }}; color: {{ $s['color'] }}; border-radius: 6px; font-size: 0.875rem;">
                    <i class="bi {{ $s['icon'] }} me-1"></i> {{ $s['label'] }}
                </span>
            </p>
        </div>

        <a href="{{ route('pemusnahan.usulan.index') }}" class="btn btn-outline-secondary shadow-sm mt-2 mt-md-0">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    {{-- ================= STATISTIK RINGKAS ================= --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card bg-primary text-white shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Total Arsip</h6>
                            <h3 class="mb-0 fw-bold">{{ $pemusnahan->details->count() }}</h3>
                        </div>
                        <i class="bi bi-archive fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card bg-danger text-white shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Keputusan Musnah</h6>
                            <h3 class="mb-0 fw-bold">{{ $pemusnahan->details->where('keputusan', 'musnah')->count() }}</h3>
                        </div>
                        <i class="bi bi-trash3 fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-secondary text-white shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Belum Dinilai</h6>
                            <h3 class="mb-0 fw-bold">{{ $pemusnahan->details->where('keputusan', 'belum_dinilai')->count() }}</h3>
                        </div>
                        <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= INFO ================= --}}
    <div class="alert alert-primary border-0 shadow-sm mb-4">
        <div class="d-flex">
            <div class="me-3">
                <i class="bi bi-info-circle-fill fs-4"></i>
            </div>
            <div>
                <strong class="d-block mb-1">Catatan</strong>
                <ul class="mb-0 ps-3">
                    <li>Arsip tetap berstatus <strong>HABIS RETENSI</strong></li>
                    <li>Keputusan ditentukan saat sidang penilaian</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ================= KETERANGAN ================= --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-card-text text-primary fs-5 me-2"></i>
                <h5 class="card-title mb-0 fw-semibold">Keterangan Pemusnahan</h5>
            </div>
        </div>
        <div class="card-body pt-0">
            <p class="mb-0 text-muted">{{ $pemusnahan->keterangan ?? '-' }}</p>
        </div>
    </div>

    {{-- ================= TAHAP SELANJUTNYA ================= --}}
    @if ($pemusnahan->status === 'draft')
    <div class="card shadow-sm border-0 mb-4" style="border-left: 4px solid #f59e0b !important;">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h6 class="mb-1 fw-semibold">
                    <i class="bi bi-signpost-split text-warning me-1"></i>
                    Tahap Selanjutnya
                </h6>
                <small class="text-muted">
                    Setelah seluruh arsip yang akan dimusnahkan dipilih, silakan lanjut ke proses Penelitian/Penilaian Arsip.
                </small>
            </div>
            <a href="{{ route('pemusnahan.sidang', $pemusnahan->id) }}" class="btn btn-warning">
                <i class="bi bi-arrow-right-circle me-1"></i>
                Penelitian / Penilaian
            </a>
        </div>
    </div>
    @endif

    {{-- ================= TAMBAH ARSIP ================= --}}
    @if ($pemusnahan->status === 'draft')
    <form action="{{ route('pemusnahan.arsip.tambah', $pemusnahan) }}" method="POST">
        @csrf

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-plus-circle text-primary fs-5 me-2"></i>
                        <h5 class="card-title mb-0 fw-semibold">Tambah Arsip ke Pemusnahan</h5>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Tambahkan Arsip Terpilih
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text"
                                   class="form-control border-start-0"
                                   id="searchArsip"
                                   placeholder="Cari uraian arsip atau tahun arsip...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="arsipTable">
                        <thead class="table-light">
                            <tr>
                                <th width="40" class="text-center">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Nama Arsip</th>
                                <th class="text-center">Tahun Arsip</th>
                                <th class="text-center">Aktif Tahun</th>
                                <th class="text-center">Inaktif Tahun</th>
                            </tr>
                        </thead>
                        <tbody id="arsipTableBody">
                            @forelse ($arsipList as $arsip)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox"
                                           name="arsip_id[]"
                                           value="{{ $arsip->id }}"
                                           class="arsip-checkbox form-check-input">
                                </td>
                                <td>{{ $arsip->uraian_arsip }}</td>
                                <td class="text-center">{{ $arsip->tahun_arsip }}</td>
                                <td class="text-center">{{ $arsip->aktif_tahun }}</td>
                                <td class="text-center">{{ $arsip->inaktif_tahun }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="bi bi-inbox fs-3 text-muted d-block mb-2"></i>
                                    <small class="text-muted">Tidak ada arsip yang tersedia untuk ditambahkan</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
    @endif

    {{-- ================= DAFTAR ARSIP DALAM PEMUSNAHAN ================= --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list-check text-primary fs-5 me-2"></i>
                <h5 class="card-title mb-0 fw-semibold">Daftar Arsip dalam Pemusnahan</h5>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="30%">Uraian Arsip</th>
                            <th width="10%" class="text-center">Tahun</th>
                            <th width="12%" class="text-center">Jumlah</th>
                            <th width="15%" class="text-center">Keputusan</th>
                            <th width="18%">Catatan</th>
                            @if ($pemusnahan->status === 'draft')
                                <th width="10%" class="text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pemusnahan->details as $i => $detail)
                        @php
                            $keputusanMap = [
                                'musnah' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'icon' => 'bi-trash3'],
                                'inaktif' => ['bg' => '#f1f5f9', 'color' => '#475569', 'icon' => 'bi-archive'],
                                'permanen' => ['bg' => '#dcfce7', 'color' => '#166534', 'icon' => 'bi-shield-check'],
                                'belum_dinilai' => ['bg' => '#fef3c7', 'color' => '#92400e', 'icon' => 'bi-hourglass-split'],
                            ];
                            $k = $keputusanMap[$detail->keputusan] ?? ['bg' => '#f8f9fa', 'color' => '#6c757d', 'icon' => 'bi-dash-circle'];
                        @endphp
                        <tr>
                            <td class="text-center fw-semibold text-muted">{{ $i + 1 }}</td>
                            <td>{{ $detail->arsip->uraian_arsip }}</td>
                            <td class="text-center">{{ $detail->arsip->tahun_arsip }}</td>
                            <td class="text-center">
                                {{ $detail->arsip->jumlah_berkas }} {{ $detail->arsip->satuan_arsip }}
                            </td>
                            <td class="text-center">
                                <span class="px-3 py-2 fw-normal d-inline-flex align-items-center" style="background-color: {{ $k['bg'] }}; color: {{ $k['color'] }}; border-radius: 6px; font-size: 0.8rem; gap: 4px;">
                                    <i class="bi {{ $k['icon'] }}"></i>
                                    {{ str_replace('_', ' ', ucfirst($detail->keputusan)) }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $detail->catatan ?? '-' }}</td>

                            @if ($pemusnahan->status === 'draft')
                                <td class="text-center">
                                    <form action="{{ route('pemusnahan.arsip.hapus', [$pemusnahan->id, $detail->arsip_id]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus arsip ini dari daftar pemusnahan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash me-1"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $pemusnahan->status === 'draft' ? 7 : 6 }}" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                <h6 class="text-muted">Belum ada arsip dalam pemusnahan ini</h6>
                                @if ($pemusnahan->status === 'draft')
                                    <small class="text-muted">Silakan tambahkan arsip dari tabel di atas</small>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

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
        transform: translateY(-1px);
    }
</style>
@endpush

@push('scripts')
<script>
document.getElementById('selectAll')?.addEventListener('change', function () {
    document.querySelectorAll('.arsip-checkbox').forEach(cb => {
        cb.checked = this.checked;
    });
});

document.getElementById('searchArsip')?.addEventListener('keyup', function () {
    let keyword = this.value.toLowerCase();
    let rows = document.querySelectorAll('#arsipTableBody tr');

    rows.forEach(function (row) {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(keyword) ? '' : 'none';
    });
});
</script>
@endpush
@endsection