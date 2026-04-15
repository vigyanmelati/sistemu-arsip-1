@extends('layouts.app')

@section('page-title', 'Detail Berita Acara')

@section('content')
<div class="card mb-3">
    <div class="card-body">

        <h5 class="mb-3">{{ $berita_acara->nomor_bap }}</h5>

        <p><strong>Tanggal:</strong> {{ $berita_acara->tanggal_bap }}</p>
        <p><strong>Status:</strong> 
            <span class="badge bg-warning">{{ $berita_acara->status }}</span>
        </p>

        <a href="{{ asset('storage/berita_acara/'.$berita_acara->file_bap) }}" 
           target="_blank" class="btn btn-primary">
            Lihat File
        </a>

    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6>Daftar Arsip</h6>
    </div>
    <div class="card-body">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kode</th>
                    <th>Judul</th>
                    <th>Tahun</th>
                </tr>
            </thead>
            <tbody>
                @forelse($berita_acara->details as $detail)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $detail->arsip->kodeKlasifikasi->kode ?? '-' }}</td>
                    <td>{{ $detail->arsip->uraian_arsip }}</td>
                    <td>{{ $detail->arsip->tahun_arsip }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">Belum ada arsip</td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</div>
@endsection