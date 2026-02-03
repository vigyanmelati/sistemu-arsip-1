@extends('layouts.app')

@section('title', 'Pemusnahan Arsip')

@section('content')
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
            <li>Arsip tetap berstatus <strong>USUL_MUSNAH</strong></li>
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
                                @if ($item->status == 'draft')
                                    <span class="badge bg-warning text-dark">Draft</span>
                                @elseif ($item->status == 'ditetapkan')
                                    <span class="badge bg-success">Ditetapkan</span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </td>

                            <td class="text-center d-flex gap-1 justify-content-center">
                                <a href="{{ route('pemusnahan.usulan.show', $item->id) }}"
                                   class="btn btn-sm btn-info">
                                    🔍 Detail
                                </a>

                                <!-- <a href="{{ route('pemusnahan.usulan.nota_dinas', $item->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    📄 Nota Dinas
                                </a> -->

                                @if ($item->status == 'draft')
                                    <form action="{{ route('pemusnahan.finalisasi', $item->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Finalisasi pemusnahan ini?')">
                                        @csrf
                                        <button class="btn btn-sm btn-success">
                                            ✔ Tetapkan
                                        </button>
                                    </form>
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
