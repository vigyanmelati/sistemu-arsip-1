@extends('layouts.app')

@section('title', 'Pemusnahan Arsip')

@section('content')
@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

<div class="container-fluid">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Pemusnahan Arsip</h4>
            <small class="text-muted">
                Daftar kegiatan pemusnahan arsip (per periode / tahun)
            </small>
        </div>

        <a href="{{ route('pemusnahan.usulan.create') }}"
           class="btn btn-primary">
            ➕ Buat Pemusnahan Baru
        </a>
    </div>

    {{-- ================= INFO ================= --}}
    <div class="alert alert-info">
        <strong>Catatan:</strong>
        <ul class="mb-0">
            <li>Setiap pemusnahan merupakan <strong>satu kegiatan / batch</strong></li>
            <li>Arsip tetap berstatus <strong>HABIS_RETENSI</strong></li>
            <li>Keputusan akhir dicatat pada dokumen pemusnahan</li>
        </ul>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="card shadow-sm">
        <div class="card-body table-responsive">

            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th>Tahun</th>
                        <th>Tanggal Usulan</th>
                        <th width="15%">Jumlah Arsip</th>
                        <th width="15%">Status</th>
                        <th width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pemusnahans as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>

                            <td class="text-center">
                                {{ $item->tahun }}
                            </td>

                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($item->tanggal_usulan)->translatedFormat('d F Y') }}
                            </td>

                            <td class="text-center">
                                {{ $item->details_count }} Arsip
                            </td>

                            <td class="text-center">
                                @switch($item->status)
                                    @case('draft')
                                        <span class="badge bg-warning text-dark">Draft</span>
                                        @break

                                    @case('diajukan_ke_anri')
                                        <span class="badge bg-primary">Diajukan ke ANRI</span>
                                        @break

                                    @case('revisi_anri')
                                        <span class="badge bg-danger">Revisi ANRI</span>
                                        @break

                                    @case('disetujui_anri')
                                        <span class="badge bg-success">Disetujui ANRI</span>
                                        @break

                                    @default
                                        <span class="badge bg-secondary">-</span>
                                @endswitch
                            </td>

                            <td class="text-center d-flex gap-1 justify-content-center">

                                {{-- DETAIL --}}
                                <a href="{{ route('pemusnahan.usulan.show', $item->id) }}"
                                class="btn btn-sm btn-info">
                                    🔍 Detail
                                </a>

                                {{-- SIDANG --}}
                                @if ($item->status == 'draft')
                                    <a href="{{ route('pemusnahan.sidang', $item->id) }}"
                                    class="btn btn-sm btn-warning">
                                        🏛 Sidang
                                    </a>
                                @endif

                                {{-- PROSES ANRI --}}
                                @if (in_array($item->status, ['diajukan_ke_anri', 'revisi_anri']))
                                    <a href="{{ route('pemusnahan.anri', $item->id) }}"
                                    class="btn btn-sm btn-primary">
                                        🏢 ANRI
                                    </a>
                                @endif

                                {{-- SIAP DIMUSNAHKAN --}}
                                @if ($item->status == 'disetujui_anri')
                                    <a href="{{ route('pemusnahan.eksekusi', $item->id) }}"
                                    class="btn btn-sm btn-success">
                                        🔥 Musnahkan
                                    </a>
                                @endif

                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Belum ada kegiatan pemusnahan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

</div>
@endsection
