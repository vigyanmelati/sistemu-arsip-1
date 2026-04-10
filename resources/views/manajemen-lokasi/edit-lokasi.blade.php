{{-- resources/views/manajemen-lokasi/edit-lokasi.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Atur Lokasi Arsip')
@section('page-subtitle', 'Isi ruangan, rak, dan box untuk arsip')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-warning">
            <h5>Atur Lokasi: {{ $arsip->uraian_arsip ?: 'Arsip #'.$arsip->id }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('manajemen-lokasi.pindah-arsip') }}" method="POST">
                @csrf
                <input type="hidden" name="arsip_id" value="{{ $arsip->id }}">
                <div class="mb-3">
                    <label>Ruangan / Lokasi</label>
                    <select name="lokasi_arsip" class="form-select" required>
                        @foreach($ruanganOptions as $key => $label)
                            <option value="{{ $key }}" {{ $arsip->lokasi_arsip == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Nomor Rak</label>
                    <input type="text" name="nomor_rak" class="form-control" value="{{ $arsip->nomor_rak }}">
                </div>
                <div class="mb-3">
                    <label>Nomor Box</label>
                    <input type="text" name="nomor_box" class="form-control" value="{{ $arsip->nomor_box }}">
                </div>
                <button type="submit" class="btn btn-primary">Simpan Lokasi</button>
                <a href="{{ route('manajemen-lokasi.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection