@extends('layouts.app')

@section('page-title', 'Data Satker')
@section('page-subtitle', 'Kelola daftar satker dan atur satker yang aktif')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Data Satker</h4>
        <a href="{{ route('superadmin.satkers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Satker
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Nama Satker</th>
                            <th>Kode Satker</th>
                            <th>Alamat</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($satkers as $satker)
                            <tr class="{{ $satker->is_active ? 'table-success' : '' }}">
                                <td>{{ $loop->iteration + ($satkers->currentPage() - 1) * $satkers->perPage() }}</td>
                                <td>{{ $satker->nama_satker }}</td>
                                <td>{{ $satker->kode_satker ?? '-' }}</td>
                                <td>{{ Str::limit($satker->alamat, 40) ?? '-' }}</td>
                                <td>
                                    @if ($satker->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        @unless ($satker->is_active)
                                            <form action="{{ route('superadmin.satkers.set-active', $satker) }}" method="POST"
                                                onsubmit="return confirm('Jadikan &quot;{{ $satker->nama_satker }}&quot; sebagai satker aktif?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    Aktifkan
                                                </button>
                                            </form>
                                        @endunless

                                        <a href="{{ route('superadmin.satkers.edit', $satker) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>

                                        <form action="{{ route('superadmin.satkers.destroy', $satker) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus satker ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                @if($satker->is_active) disabled title="Satker aktif tidak bisa dihapus" @endif>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Belum ada data satker.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $satkers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection