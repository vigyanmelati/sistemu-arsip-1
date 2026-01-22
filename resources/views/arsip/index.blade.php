@extends('layouts.app')

@section('page-title', 'Kelola Arsip')
@section('page-subtitle', 'Manajemen Data Arsip Digital')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Arsip</h5>
            <div>
                <a href="{{ route('arsip.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Arsip
                </a>
                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="bi bi-filter"></i> Filter
                </button>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Search Bar -->
        <form method="GET" action="{{ route('arsip.index') }}" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan kode, judul, atau sub bagian..." value="{{ request('search') }}">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </form>
        
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        
        <!-- Table -->
        <div class="table-responsive">
         <!-- Ganti bagian tabel head dan body -->
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode Klasifikasi</th>
                        <th>Judul Arsip</th>
                        <th>Sub Bagian</th>
                        <th>Tahun</th>
                        <th>Rak/Box</th>
                        <th>Aktif (thn)</th>
                        <th>Inaktif (thn)</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arsips as $arsip)
                    <tr>
                        <td>{{ $loop->iteration + ($arsips->currentPage() - 1) * $arsips->perPage() }}</td>
                        <td>{{ $arsip->kodeKlasifikasi->kode ?? 'N/A' }}</td>
                        <td>{{ $arsip->uraian_arsip }}</td>
                        <td>{{ $arsip->subBagian->nama_sub_bagian ?? 'N/A' }}</td>
                        <td>{{ $arsip->tahun_arsip }}</td>
                        <td>Rak: {{ $arsip->nomor_rak }} | Box: {{ $arsip->nomor_box }}</td>
                        <td>{{ $arsip->aktif_tahun }}</td>
                        <td>{{ $arsip->inaktif_tahun }}</td>
                        <td>
                            @php
                                $statusColors = [
                                    'aktif' => 'success',
                                    'inaktif' => 'warning',
                                    'musnah' => 'secondary',
                                    'permanen' => 'primary'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$arsip->status_arsip] ?? 'secondary' }}">
                                {{ ucfirst($arsip->status_arsip) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('arsip.show', $arsip->id) }}" class="btn btn-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('arsip.edit', $arsip->id) }}" class="btn btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('arsip.destroy', $arsip->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus arsip ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center">Tidak ada data arsip</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Menampilkan {{ $arsips->firstItem() }} - {{ $arsips->lastItem() }} dari {{ $arsips->total() }} arsip
            </div>
            {{ $arsips->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Arsip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('arsip.index') }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status Arsip</label>
                        <select name="status_arsip" class="form-select">
                            <option value="">Semua Status</option>
                            @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('status_arsip') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tahun Arsip</label>
                        <select name="tahun_arsip" class="form-select">
                            <option value="">Semua Tahun</option>
                            @foreach($tahunOptions as $tahun)
                            <option value="{{ $tahun }}" {{ request('tahun_arsip') == $tahun ? 'selected' : '' }}>
                                {{ $tahun }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sub Bagian</label>
                        <select name="sub_bagian" class="form-select">
                            <option value="">Semua Sub Bagian</option>
                            @foreach($subBagianOptions as $subBagian)
                            <option value="{{ $subBagian }}" {{ request('sub_bagian') == $subBagian ? 'selected' : '' }}>
                                {{ $subBagian }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('arsip.index') }}" class="btn btn-secondary">Reset</a>
                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection