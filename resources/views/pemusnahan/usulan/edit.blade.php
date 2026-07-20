@extends('layouts.app')

@section('title', 'Edit Usulan Pemusnahan')

@section('content')
<div class="container-fluid py-4">
    <h4 class="mb-4 fw-bold">
        <i class="bi bi-pencil-square text-info me-2"></i>
        Edit Usulan Pemusnahan
    </h4>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('pemusnahan.usulan.update', $pemusnahan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Tahun</label>
                    <input type="number" name="tahun" class="form-control"
                           value="{{ old('tahun', $pemusnahan->tahun) }}" required>
                    @error('tahun')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $pemusnahan->keterangan) }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('pemusnahan.usulan.show', $pemusnahan->id) }}" class="btn btn-outline-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection