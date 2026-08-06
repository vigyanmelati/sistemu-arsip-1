@extends('layouts.app')

@section('page-title', 'Data Satker')
@section('page-subtitle', 'Kelola daftar satker dan atur satker yang aktif')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Satker</h4>
        <a href="{{ route('superadmin.satkers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('superadmin.satkers.update', $satker) }}" method="POST">
                @csrf
                @method('PUT')
                @include('satkers._form', ['satker' => $satker])

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                    <a href="{{ route('superadmin.satkers.index') }}" class="btn btn-light">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection