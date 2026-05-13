@extends('layouts.app')

@section('page-title', 'Edit Berita Acara')

@section('content')
<div class="card">
    <div class="card-body">

        <form action="{{ route('berita-acara.update', $berita_acara->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nomor BAP</label>
                <input type="text" name="nomor_bap" value="{{ $berita_acara->nomor_bap }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal_bap" value="{{ $berita_acara->tanggal_bap }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">File Baru (opsional)</label>
                <input type="file" name="file_bap" class="form-control">
            </div>

            <div class="mb-3">
                <a href="{{ asset('storage/berita_acara/'.$berita_acara->file_bap) }}" target="_blank">
                    Lihat File Lama
                </a>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('berita-acara.index') }}" class="btn btn-secondary">Kembali</a>
                <button class="btn btn-warning">Update</button>
            </div>

        </form>

    </div>
</div>
@endsection