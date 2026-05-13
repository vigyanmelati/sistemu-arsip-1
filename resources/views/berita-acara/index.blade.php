@extends('layouts.app')

@section('page-title', 'Berita Acara')
@section('page-subtitle', 'Manajemen Berita Acara Pemindahan Arsip')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Berita Acara</h5>
            <a href="{{ route('berita-acara.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle"></i>
                <span>Tambah BAP</span>
            </a>
        </div>
    </div>

    <div class="card-body">

        {{-- ALERT --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nomor BAP</th>
                        <th>Tanggal</th>
                        <th>Jumlah Arsip</th>
                        <th>Status</th>
                        <th>File</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($baps as $bap)
                    <tr>
                        <td>{{ $loop->iteration + ($baps->currentPage() - 1) * $baps->perPage() }}</td>

                        <td>
                            <strong>{{ $bap->nomor_bap }}</strong>
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse($bap->tanggal_bap)->format('d M Y') }}
                        </td>

                        <td>
                            <span class="badge bg-info">
                                {{ $bap->details->count() }} Arsip
                            </span>
                        </td>

                        <td>
                            @php
                                $colors = [
                                    'DIAJUKAN' => 'warning',
                                    'DITERIMA' => 'success',
                                    'DITOLAK' => 'danger',
                                    'SELESAI' => 'info'
                                ];
                            @endphp

                            <span class="badge bg-{{ $colors[$bap->status] ?? 'secondary' }}">
                                {{ $bap->status }}
                            </span>
                        </td>

                        <td>
                            @if($bap->file_bap)
                                <a href="{{ asset('storage/berita_acara/'.$bap->file_bap) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark"></i> Lihat
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <div class="btn-group btn-group-sm" style="gap:5px;">
                                <a href="{{ route('berita-acara.show', $bap->id) }}" class="btn btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="{{ route('berita-acara.edit', $bap->id) }}" class="btn btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form action="{{ route('berita-acara.destroy', $bap->id) }}" method="POST" onsubmit="return confirm('Hapus berita acara ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="bi bi-folder-x text-muted"></i>
                            <p class="text-muted mb-0">Belum ada berita acara</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Menampilkan {{ $baps->firstItem() }} - {{ $baps->lastItem() }} dari {{ $baps->total() }} data
            </div>

            {{ $baps->links('pagination::bootstrap-5') }}
        </div>

    </div>
</div>
@endsection