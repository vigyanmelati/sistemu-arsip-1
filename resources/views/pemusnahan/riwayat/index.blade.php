@extends('layouts.app')

@section('title', 'Riwayat Pemusnahan Arsip')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">📚 Riwayat Pemusnahan Arsip</h4>
            <small class="text-muted">
                Daftar kegiatan pemusnahan arsip yang telah dilaksanakan
            </small>
        </div>
    </div>

    {{-- INFO --}}
    <div class="alert alert-secondary">
        <ul class="mb-0">
            <li>Setiap baris merupakan <strong>satu batch pemusnahan</strong></li>
            <li>Data bersifat <strong>final & read-only</strong></li>
            <li>Dokumen pemusnahan dikelola pada detail</li>
        </ul>
    </div>

    {{-- TABLE --}}
    <div class="card shadow-sm">
        <div class="card-body table-responsive">

            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th>Tahun</th>
                        <th>Tanggal Pemusnahan</th>
                        <th width="15%">Jumlah Arsip</th>
                        <th width="15%">Status</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>

                @forelse ($pemusnahans as $i => $item)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>

                        <td class="text-center">
                            {{ $item->tahun }}
                        </td>

                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($item->tanggal_pemusnahan)
                                ->translatedFormat('d F Y') }}
                        </td>

                        <td class="text-center">
                            {{ $item->details_count }} Arsip
                        </td>

                        <td class="text-center">
                            <span class="badge bg-danger">
                                Dimusnahkan
                            </span>
                        </td>

                        <td class="text-center">
                            <a href="{{ route('pemusnahan.riwayat.show', $item->id) }}"
                               class="btn btn-sm btn-info">
                                🔍 Lihat Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"
                            class="text-center text-muted">
                            Belum ada riwayat pemusnahan.
                        </td>
                    </tr>
                @endforelse

                </tbody>
            </table>

        </div>
    </div>

</div>
@endsection
