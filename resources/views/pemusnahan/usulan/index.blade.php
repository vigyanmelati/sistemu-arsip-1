@extends('layouts.app') 
{{-- sesuaikan dengan layout kamu --}}

@section('title', 'Usulan Pemusnahan Arsip')

@section('content')
<div class="container-fluid">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">🟡 TAHAP 1 — Usulan Pemusnahan Arsip</h4>
            <small class="text-muted">
                Identifikasi Arsip Usul Musnah (Internal KPU Provinsi)
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('pemusnahan.usulan.nota_dinas_word') }}"
               class="btn btn-primary">
                📄 Download Template Nota Dinas (Word)
            </a>

            <a href="{{ route('pemusnahan.usulan.excel') }}"
               class="btn btn-success">
                📊 Export Daftar Arsip (Excel)
            </a>
        </div>
    </div>

    {{-- ================= INFO BOX ================= --}}
    <div class="alert alert-info">
        <strong>Kondisi Arsip:</strong>
        <ul class="mb-0">
            <li>✔ Masa Aktif & Inaktif telah terlewati</li>
            <li>✔ JRA: <strong>MUSNAH</strong></li>
            <li>✔ Status Sistem: <strong>USUL_MUSNAH</strong></li>
        </ul>
    </div>

    {{-- ================= TABEL ARSIP ================= --}}
    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th>Jenis Arsip</th>
                        <th width="10%">Tahun</th>
                        <th width="15%">Jumlah</th>
                        <th width="15%">Tingkat Perkembangan</th>
                        <th width="10%">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($arsip as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $item->uraian_arsip }}</td>
                            <td class="text-center">{{ $item->tahun_arsip }}</td>
                            <td class="text-center">
                                {{ $item->jumlah_berkas }} {{ $item->satuan_arsip }}
                            </td>
                            <td class="text-center">
                                {{ $item->tingkat_perkembangan }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">Baik</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Tidak ada arsip usul musnah.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
