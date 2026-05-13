@extends('layouts.app')

@section('page-title', 'Tambah Berita Acara')
@section('page-subtitle', 'Input Data Berita Acara Pemindahan')

@section('content')
<div class="card">
    <div class="card-body">

        <form action="{{ route('berita-acara.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nomor BAP</label>
                <input type="text" name="nomor_bap" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Tanggal BAP</label>
                <input type="date" name="tanggal_bap" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">File BAP</label>
                <input type="file" name="file_bap" class="form-control" required>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('berita-acara.index') }}" class="btn btn-secondary">Kembali</a>
                <button class="btn btn-primary">Simpan</button>
            </div>

        </form>

    </div>
</div>
@endsection