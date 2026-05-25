@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h4>Surat Masuk</h4>

        <a href="{{ route('surat-masuk.create') }}"
           class="btn btn-primary">
            Tambah
        </a>
    </div>

    <div class="card-body">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No Surat</th>
                    <th>Perihal</th>
                    <th>Instansi</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                @foreach($surat as $item)

                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->nomor_dokumen }}</td>
                    <td>{{ $item->perihal }}</td>
                    <td>{{ $item->instansi_satker }}</td>

                    <td>

                        <a href="{{ route('surat-masuk.edit',$item->id) }}"
                           class="btn btn-warning btn-sm">
                            Edit
                        </a>

                        <a href="{{ route('surat-masuk.disposisi',$item->id) }}"
                           target="_blank"
                           class="btn btn-info btn-sm">
                            Disposisi
                        </a>

                        <form action="{{ route('surat-masuk.destroy',$item->id) }}"
                              method="POST"
                              style="display:inline">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger btn-sm">
                                Hapus
                            </button>
                        </form>

                    </td>
                </tr>

                @endforeach

            </tbody>

        </table>

    </div>
</div>

@endsection