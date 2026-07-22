@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h4>Input Dokumen</h4>
    </div>

    <div class="card-body">

        {{-- ALERT SUCCESS --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- ALERT ERROR --}}
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- VALIDATION ERROR --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Terjadi Kesalahan!</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('surat-masuk.store') }}"
              method="POST"
              enctype="multipart/form-data" id="formSuratMasuk">

            @csrf

            <div class="row">

                {{-- SUB BAGIAN --}}
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">
                        Sub Bagian*
                    </label>

                    <select name="sub_bagian_id"
                            class="form-control">

                        <option value="">
                            -- Pilih Sub Bagian --
                        </option>

                        @foreach($subBagians as $sub)
                            <option value="{{ $sub->id }}"
                                {{ old('sub_bagian_id') == $sub->id ? 'selected' : '' }}>
                                {{ $sub->nama_sub_bagian }}
                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- INSTANSI --}}
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-bold">
                        Instansi/Satker*
                    </label>

                    <input type="text"
                           name="instansi_satker"
                           class="form-control"
                           placeholder="Enter Text"
                           value="{{ old('instansi_satker') }}">
                </div>

            </div>

            <div class="row">

                {{-- TANGGAL DOKUMEN --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        Tanggal Dokumen*
                    </label>

                    <input type="date"
                           name="tanggal_dokumen"
                           class="form-control"
                           value="{{ old('tanggal_dokumen') }}">
                </div>

                {{-- TANGGAL PENYELESAIAN --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        Tanggal Penyelesaian*
                    </label>

                    <input type="date"
                           name="tanggal_penyelesaian"
                           class="form-control"
                           value="{{ old('tanggal_penyelesaian') }}">
                </div>

            </div>

            <div class="row">

                {{-- NOMOR DOKUMEN --}}
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-bold">
                        Nomor Dokumen*
                    </label>

                    <input type="text"
                           name="nomor_dokumen"
                           class="form-control"
                           placeholder="Enter Text"
                           value="{{ old('nomor_dokumen') }}">
                </div>

                {{-- NOMOR AGENDA --}}
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">
                        Nomor Agenda*
                    </label>

                    <input type="text"
                           name="nomor_agenda"
                           class="form-control"
                           placeholder="Enter Text"
                           value="{{ old('nomor_agenda') }}">
                </div>

            </div>

            <div class="row">

                {{-- KEPADA --}}
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">
                        Kepada*
                    </label>

                    <input type="text"
                           name="kepada"
                           class="form-control"
                           placeholder="Enter Text"
                           value="{{ old('kepada') }}">
                </div>

            </div>

            <div class="row">

                {{-- PERIHAL --}}
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">
                        Perihal*
                    </label>

                    <textarea name="perihal"
                              class="form-control"
                              rows="3"
                              placeholder="Enter Text">{{ old('perihal') }}</textarea>
                </div>

            </div>

            <div class="row">

                {{-- CATATAN --}}
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">
                        Catatan
                    </label>

                    <textarea name="catatan"
                              class="form-control"
                              rows="3"
                              placeholder="Enter Text">{{ old('catatan') }}</textarea>
                </div>

            </div>

            <div class="row">

                {{-- FILE --}}
                <div class="col-md-12 mb-4">
                    <label class="form-label fw-bold">
                        File Input
                    </label>

                    <input type="file"
                           name="file_input"
                           class="form-control">
                </div>

            </div>

            <button type="submit"
                    class="btn btn-primary" id="btnSubmitSuratMasuk">
                Submit
            </button>

            <button type="reset"
                    class="btn btn-secondary">
                Reset
            </button>

            <a href="{{ route('surat-masuk.index') }}"
               class="btn btn-danger">
                Cancel
            </a>

        </form>

    </div>
</div>
@push('scripts')
<script>
document.getElementById('formSuratMasuk').addEventListener('submit', function (e) {
    const btn = document.getElementById('btnSubmitSuratMasuk');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
});
</script>
@endpush
@endsection