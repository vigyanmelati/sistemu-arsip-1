@extends('layouts.app')

@section('title','Riwayat Pemusnahan Arsip')

@section('content')
<div class="container-fluid">

<h4 class="mb-4">📚 Riwayat Pemusnahan Arsip</h4>

@forelse ($pemusnahans as $pemusnahan)

<div class="card mb-4 shadow-sm">

    {{-- HEADER --}}
    <div class="card-header bg-danger text-white">
        <strong>Tahun {{ $pemusnahan->tahun }}</strong>
        <span class="float-end">
            Dimusnahkan:
            {{ \Carbon\Carbon::parse($pemusnahan->tanggal_pemusnahan)
                ->translatedFormat('d F Y') }}
        </span>
    </div>

    {{-- BODY --}}
    <div class="card-body table-responsive">

        <h6 class="mb-3">Daftar Arsip Dimusnahkan</h6>

        <table class="table table-bordered table-sm align-middle">
            <thead class="table-light text-center">
                <tr>
                    <th width="5%">No</th>
                    <th>Uraian Arsip</th>
                    <th>Tahun</th>
                    <th>Jumlah</th>
                    <th>Tingkat</th>
                    <th>Retensi</th>
                </tr>
            </thead>
            <tbody>

            @foreach ($pemusnahan->details as $i => $detail)
                <tr>
                    <td class="text-center">{{ $i+1 }}</td>
                    <td>{{ $detail->arsip->uraian_arsip }}</td>
                    <td class="text-center">{{ $detail->arsip->tahun_arsip }}</td>
                    <td class="text-center">
                        {{ $detail->arsip->jumlah_berkas }}
                        {{ $detail->arsip->satuan_arsip }}
                    </td>
                    <td class="text-center">
                        {{ ucfirst($detail->arsip->tingkat_perkembangan) }}
                    </td>
                    <td class="text-center">
                        Aktif {{ $detail->arsip->aktif_tahun }} th /
                        Inaktif {{ $detail->arsip->inaktif_tahun }} th
                    </td>
                </tr>
            @endforeach

            </tbody>
        </table>

        {{-- DOKUMEN --}}
        <hr>
        <h6>📎 Dokumen Pemusnahan</h6>

        @php
            $dokumen = $pemusnahan->dokumen_pemusnahan ?? [];
        @endphp

        @if(count($dokumen))
        <ul class="list-group list-group-flush">
            @foreach ($dokumen as $label => $file)
            <li class="list-group-item d-flex justify-content-between">
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
        <small class="text-muted fst-italic">
            Dokumen pemusnahan belum diunggah
        </small>
        @endif


        <ul class="list-group list-group-flush">
            @foreach ($dokumen as $label => $file)
            <li class="list-group-item d-flex justify-content-between">
                {{ ucwords(str_replace('_',' ',$label)) }}
                <a href="{{ asset('storage/'.$file) }}"
                   target="_blank"
                   class="btn btn-sm btn-outline-primary">
                    Lihat
                </a>
            </li>
            @endforeach
        </ul>

    </div>
</div>

@empty
<div class="alert alert-info">
    Belum ada riwayat pemusnahan arsip.
</div>
@endforelse

</div>
@endsection
