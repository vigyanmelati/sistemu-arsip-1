@extends('layouts.app')

@section('title', 'Detail Riwayat Pemusnahan')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">📚 Detail Pemusnahan Arsip</h4>
            <small class="text-muted">
                Informasi lengkap kegiatan pemusnahan arsip
            </small>
        </div>

        <a href="{{ route('pemusnahan.riwayat') }}"
           class="btn btn-secondary">
            ← Kembali
        </a>
    </div>

    {{-- INFO PEMUSNAHAN --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body row">

            <div class="col-md-4">
                <strong>Tahun</strong>
                <div>{{ $pemusnahan->tahun }}</div>
            </div>

            <div class="col-md-4">
                <strong>Tanggal Pemusnahan</strong>
                <div>
                    {{ \Carbon\Carbon::parse($pemusnahan->tanggal_pemusnahan)
                        ->translatedFormat('d F Y') }}
                </div>
            </div>

            <div class="col-md-4">
                <strong>Status</strong><br>
                <span class="badge bg-danger mt-1">
                    DIMUSNAHKAN
                </span>
            </div>

            @if($pemusnahan->catatan_anri)
            <div class="col-12 mt-3">
                <strong>Catatan ANRI</strong>
                <div class="alert alert-warning mb-0">
                    {{ $pemusnahan->catatan_anri }}
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- DAFTAR ARSIP --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <strong>📋 Daftar Arsip Dimusnahkan</strong>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th>Uraian Arsip</th>
                        <th>Tahun</th>
                        <th>Jumlah</th>
                        <th>Tingkat</th>
                        <th>Retensi</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>

                @foreach ($pemusnahan->details as $i => $detail)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>

                        <td>{{ $detail->arsip->uraian_arsip }}</td>

                        <td class="text-center">
                            {{ $detail->arsip->tahun_arsip }}
                        </td>

                        <td class="text-center">
                            {{ $detail->arsip->jumlah_berkas }}
                            {{ $detail->arsip->satuan_arsip }}
                        </td>

                        <td class="text-center">
                            {{ ucfirst($detail->arsip->tingkat_perkembangan) }}
                        </td>

                        <td class="text-center">
                            Aktif {{ $detail->arsip->aktif_tahun }} /
                            Inaktif {{ $detail->arsip->inaktif_tahun }} 
                        </td>

                        <td class="text-center">
                            <a href="{{ route('arsip.show', $detail->arsip->id) }}"
                               class="btn btn-sm btn-outline-info">
                                🔍 Detail
                            </a>
                        </td>
                    </tr>
                @endforeach

                </tbody>
            </table>
        </div>
    </div>

    {{-- DOKUMEN PEMUSNAHAN --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <strong>📎 Dokumen Pemusnahan</strong>
        </div>

        <div class="card-body">

            @php
                $dokumen = $pemusnahan->dokumen_pemusnahan ?? [];
            @endphp

            {{-- LIST DOKUMEN --}}
            @if(count($dokumen))
                <ul class="list-group list-group-flush mb-3">
                    @foreach ($dokumen as $label => $file)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ ucwords(str_replace('_',' ',$label)) }}

                            <a href="{{ asset('storage/'.$file) }}"
                               target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                Lihat
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted fst-italic">
                    Dokumen pemusnahan belum diunggah.
                </p>
            @endif

            {{-- FORM UPLOAD --}}
            <form action="{{ route('pemusnahan.eksekusi.simpan', $pemusnahan->id) }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Berita Acara Pemusnahan</label>
                        <input type="file"
                               name="dokumen[berita_acara]"
                               class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Foto Dokumentasi</label>
                        <input type="file"
                               name="dokumen[dokumentasi]"
                               class="form-control">
                    </div>
                </div>

                <div class="mt-3">
                    <button class="btn btn-success">
                        💾 Simpan Dokumen
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
