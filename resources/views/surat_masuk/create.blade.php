@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h4>Tambah Surat Masuk</h4>
    </div>

    <div class="card-body">

        <form action="{{ route('surat-masuk.store') }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf

            <div class="mb-3">
                <label>Sub Bagian</label>

                <select name="sub_bagian_id" class="form-control">
                    @foreach($subBagians as $sub)
                        <option value="{{ $sub->id }}">
                            {{ $sub->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Instansi</label>
                <input type="text"
                       name="instansi_satker"
                       class="form-control">
            </div>

            <div class="mb-3">
                <label>No Surat</label>
                <input type="text"
                       name="nomor_dokumen"
                       class="form-control">
            </div>

            <div class="mb-3">
                <label>No Agenda</label>
                <input type="text"
                       name="nomor_agenda"
                       class="form-control">
            </div>

            <div class="mb-3">
                <label>Kepada</label>
                <input type="text"
                       name="kepada"
                       class="form-control">
            </div>

            <div class="mb-3">
                <label>Perihal</label>
                <textarea name="perihal"
                          class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label>Catatan</label>
                <textarea name="catatan"
                          class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label>Tanggal Dokumen</label>
                <input type="date"
                       name="tanggal_dokumen"
                       class="form-control">
            </div>

            <div class="mb-3">
                <label>Tanggal Penyelesaian</label>
                <input type="date"
                       name="tanggal_penyelesaian"
                       class="form-control">
            </div>

            <div class="mb-3">
                <label>File</label>
                <input type="file"
                       name="file_input"
                       class="form-control">
            </div>

            <button class="btn btn-primary">
                Simpan
            </button>

        </form>

    </div>
</div>

@endsection