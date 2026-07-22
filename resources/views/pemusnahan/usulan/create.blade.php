@extends('layouts.app')

@section('title', 'Buat Pemusnahan Arsip')

@section('content')
<div class="container-fluid">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Buat Kegiatan Pemusnahan Arsip</h4>
            <small class="text-muted">
                Pembuatan kegiatan / batch pemusnahan arsip
            </small>
        </div>

        <a href="{{ route('pemusnahan.usulan.index') }}"
           class="btn btn-secondary">
            ⬅ Kembali
        </a>
    </div>

    {{-- ================= INFO ================= --}}
    <div class="alert alert-info">
        <strong>Catatan:</strong>
        <ul class="mb-0">
            <li>Tanggal pemusnahan ditentukan <strong>setelah persetujuan</strong></li>
            <li>Status awal kegiatan adalah <strong>Draft</strong></li>
            <li>Arsip tidak dimodifikasi</li>
        </ul>
    </div>

    {{-- ================= FORM ================= --}}
    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('pemusnahan.usulan.store') }}" method="POST" id="formPemusnahan">>
                @csrf

                <div class="row">

                    {{-- TAHUN --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tahun Usulan Pemusnahan</label>
                        <input type="number"
                               name="tahun"
                               class="form-control"
                               value="{{ old('tahun', date('Y')) }}"
                               required>
                    </div>

                    {{-- STATUS --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <input type="text"
                               class="form-control"
                               value="Draft"
                               readonly>
                        <input type="hidden" name="status" value="draft">
                    </div>

                    {{-- KETERANGAN --}}
                    <div class="col-12 mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Contoh: Usulan pemusnahan arsip inaktif tahun 2025">{{ old('keterangan') }}</textarea>
                    </div>

                </div>

                {{-- ACTION --}}
                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary" id="btnSubmitPemusnahan">
                        💾 Simpan & Lanjutkan
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

@push('scripts')
<script>
document.getElementById('formPemusnahan').addEventListener('submit', function (e) {
    const btn = document.getElementById('btnSubmitPemusnahan');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
});
</script>
@endpush
@endsection
