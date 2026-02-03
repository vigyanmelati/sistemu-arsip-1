@extends('layouts.app')

@section('title', 'Detail Pemusnahan Arsip')

@section('content')
<div class="container-fluid">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                Detail Pemusnahan Arsip – Tahun {{ $pemusnahan->tahun }}
            </h4>
            <small class="text-muted">
                Status:
                <span class="badge bg-warning text-dark">
                    {{ strtoupper($pemusnahan->status) }}
                </span>
            </small>
        </div>

        <a href="{{ route('pemusnahan.usulan.index') }}" class="btn btn-secondary">
            ⬅ Kembali
        </a>
    </div>

    {{-- ================= INFO ================= --}}
    <div class="alert alert-info">
        <strong>Catatan:</strong>
        <ul class="mb-0">
            <li>Arsip tetap berstatus <b>USUL_MUSNAH</b></li>
            <li>Keputusan ditentukan saat sidang penilaian</li>
        </ul>
    </div>

    {{-- ================= TAMBAH ARSIP (SELECT ALL) ================= --}}
    @if ($pemusnahan->status === 'draft')
    <form action="{{ route('pemusnahan.arsip.tambah', $pemusnahan) }}" method="POST">
        @csrf


        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between">
                <strong>Tambah Arsip ke Pemusnahan</strong>

                <button type="submit" class="btn btn-primary btn-sm">
                    ➕ Tambahkan Arsip Terpilih
                </button>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-bordered">
    <thead>
        <tr>
            <th width="40">
                <input type="checkbox" id="selectAll">
            </th>
            <th>Nama Arsip</th>
            <!-- <th>Kode</th> -->
        </tr>
    </thead>
    <tbody>
        @foreach ($arsipList as $arsip)
        <tr>
            <td>
                <input type="checkbox"
                       name="arsip_id[]"
                       value="{{ $arsip->id }}"
                       class="arsip-checkbox">
            </td>
            <td>{{ $arsip->uraian_arsip }}</td>
            <!-- <td>{{ $arsip->kode }}</td> -->
        </tr>
        @endforeach
    </tbody>
</table>


            </div>
        </div>
    </form>
    @endif

    {{-- ================= DAFTAR ARSIP DALAM PEMUSNAHAN ================= --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <strong>Daftar Arsip dalam Pemusnahan</strong>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>No</th>
                        <th>Uraian Arsip</th>
                        <th>Tahun</th>
                        <th>Jumlah</th>
                        <th>Keputusan</th>
                        <th>Catatan</th>
                        @if ($pemusnahan->status === 'draft')
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pemusnahan->details as $i => $detail)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $detail->arsip->uraian_arsip }}</td>
                            <td class="text-center">{{ $detail->arsip->tahun_arsip }}</td>
                            <td class="text-center">
                                {{ $detail->arsip->jumlah_berkas }}
                                {{ $detail->arsip->satuan_arsip }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">
                                    {{ str_replace('_', ' ', $detail->keputusan) }}
                                </span>
                            </td>
                            <td>{{ $detail->catatan ?? '-' }}</td>

                            @if ($pemusnahan->status === 'draft')
                                <td class="text-center">
                                    <form action="{{ route('pemusnahan.arsip.hapus', [$pemusnahan->id, $detail->arsip_id]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus arsip ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            🗑 Hapus
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                Belum ada arsip dalam pemusnahan ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ================= SCRIPT SELECT ALL ================= --}}
<script>
document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('.arsip-checkbox').forEach(cb => {
        cb.checked = this.checked;
    });
});
</script>

@endsection
