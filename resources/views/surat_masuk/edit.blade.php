@extends('layouts.app')

@section('content')
@push('styles')
<style>
    .form-check {
        padding-left: 1.8em;
    }
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    .form-check-label {
        cursor: pointer;
        user-select: none;
    }
    .card-body .form-check:hover .form-check-label {
        color: #0d6efd;
    }
</style>
@endpush
<div class="card">
    <div class="card-header">
        <h4>Edit Dokumen</h4>
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

        <form action="{{ route('surat-masuk.update', $surat->id) }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="row">

                {{-- INSTANSI --}}
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">
                        Instansi/Satker*
                    </label>

                    <select name="instansi_id" class="form-select" required>
                        <option value="">-- Pilih Instansi/Satker --</option>
                        @foreach($instansis as $instansi)
                            <option value="{{ $instansi->id }}" @selected(old('instansi_id', $surat->instansi_id) == $instansi->id)>{{ $instansi->nama_instansi }}</option>
                        @endforeach
                    </select>
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
           value="{{ old('tanggal_dokumen', optional($surat->tanggal_dokumen)->format('Y-m-d')) }}">
</div>

{{-- TANGGAL PENYELESAIAN --}}
<div class="col-md-6 mb-3">
    <label class="form-label fw-bold">
        Tanggal Penyelesaian*
    </label>

    <input type="date"
           name="tanggal_penyelesaian"
           class="form-control"
           value="{{ old('tanggal_penyelesaian', optional($surat->tanggal_penyelesaian)->format('Y-m-d')) }}">
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
                           value="{{ old('nomor_dokumen', $surat->nomor_dokumen) }}">
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
                           value="{{ old('nomor_agenda', $surat->nomor_agenda) }}">
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
                           value="{{ old('kepada', $surat->kepada) }}">
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
                              placeholder="Enter Text">{{ old('perihal', $surat->perihal) }}</textarea>
                </div>

            </div>

            <div class="row">

                @php($selectedTujuan = old('tujuan_disposisi_ids', $surat->tujuanDisposisis->pluck('id')->all()))
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">Tujuan Disposisi</label>
                    <select name="tujuan_disposisi_ids[]" class="form-select" multiple size="5">
                        @foreach($tujuanDisposisis as $tujuan)
                            <option value="{{ $tujuan->id }}" @selected(in_array($tujuan->id, $selectedTujuan))>{{ $tujuan->nama_tujuan }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Opsional. Tahan Ctrl (Windows) untuk memilih lebih dari satu tujuan.</small>
                </div>

                {{-- CATATAN --}}
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">
                        Catatan
                    </label>

                    <textarea name="catatan"
                              class="form-control"
                              rows="3"
                              placeholder="Enter Text">{{ old('catatan', $surat->catatan) }}</textarea>
                </div>

            </div>

           <div class="row">

    {{-- SIFAT --}}
    <div class="col-md-12 mb-4">
        <label class="form-label fw-bold mb-2">Sifat</label>
        <div class="card">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap gap-3">
                    @foreach([
                        'biasa' => 'Biasa Mendesak',
                        'penting' => 'Penting/Segera',
                        'khusus' => 'Perlu Perhatian Khusus',
                        'batas_waktu' => 'Perlu Perhatian Batas Waktu',
                    ] as $value => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sifat"
                                   value="{{ $value }}" id="sifat_{{ $value }}"
                                   @checked(old('sifat', $surat->sifat) === $value)>
                            <label class="form-check-label" for="sifat_{{ $value }}">
                                {{ $label }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <small class="text-muted">Opsional — pilih salah satu tingkat kepentingan surat.</small>
    </div>

    {{-- BANTUAN --}}
    <div class="col-md-12 mb-3">
        <label class="form-label fw-bold mb-2">Mohon Bantuan Saudara Untuk</label>
        <div class="card">
            <div class="card-body py-3">
                <div class="row g-2">
                    @foreach($opsiBantuan as $key => $label)
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="bantuan[]"
                                       value="{{ $key }}" id="bantuan_{{ $key }}"
                                       @checked(in_array($key, $bantuanTerpilih))>
                                <label class="form-check-label" for="bantuan_{{ $key }}">
                                    {{ $label }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <small class="text-muted">Opsional — boleh pilih lebih dari satu.</small>
    </div>

</div>

            <div class="row">

                {{-- FILE --}}
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">
                        File Input
                    </label>

                    <input type="file"
                           name="file_input"
                           class="form-control">

                    @if($surat->file_input)
                        <div class="mt-2">

                            <a href="{{ asset('storage/surat_masuk/' . $surat->file_input) }}"
                               target="_blank"
                               class="btn btn-sm btn-info">

                                Lihat File Lama

                            </a>

                            <small class="text-muted d-block mt-1">
                                {{ $surat->file_input }}
                            </small>

                        </div>
                    @endif
                </div>

            </div>

            <button type="submit"
                    class="btn btn-primary">
                Update
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

@endsection
