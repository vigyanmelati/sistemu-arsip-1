@extends('layouts.app')

@section('title', 'Kode Klasifikasi')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Master Data Kode Klasifikasi</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            + Tambah Kode
        </button>
    </div>

    {{-- ALERT --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body">

            {{-- SEARCH BOX --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari kode / uraian...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="tabelKlasifikasi">
                    <thead class="table-light">
    <tr>
        <th width="5%">No</th>
        <th width="20%" class="sortable" data-col="1">
            <div class="d-flex justify-content-between align-items-center">
                <span>Kode</span>
                <span class="sort-icon">
                    <i class="bi bi-arrow-down-up"></i>
                </span>
            </div>
        </th>
        <th class="sortable" data-col="2">
            <div class="d-flex justify-content-between align-items-center">
                <span>Uraian</span>
                <span class="sort-icon">
                    <i class="bi bi-arrow-down-up"></i>
                </span>
            </div>
        </th>
        <th width="15%">Aksi</th>
    </tr>
</thead>
                    <tbody id="tabelBody">
                        @forelse ($data as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $item->kode }}</strong></td>
                                <td>{{ $item->uraian }}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal{{ $item->id }}">
                                        Edit
                                    </button>

                                    <form action="{{ route('superadmin.kode-klasifikasis.destroy', $item->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin hapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            {{-- MODAL EDIT --}}
                            <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">

                                        <form method="POST"
                                            action="{{ route('superadmin.kode-klasifikasis.update', $item->id) }}">
                                            @csrf
                                            @method('PUT')

                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Kode Klasifikasi</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Kode</label>
                                                    <input type="text"
                                                        name="kode"
                                                        class="form-control"
                                                        value="{{ $item->kode }}"
                                                        required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Uraian</label>
                                                    <textarea name="uraian"
                                                            class="form-control"
                                                            rows="3"
                                                            required>{{ $item->uraian }}</textarea>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button class="btn btn-primary">Update</button>
                                            </div>

                                        </form>

                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Data belum tersedia</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content"
              method="POST"
              action="{{ route('superadmin.kode-klasifikasis.store') }}">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Tambah Kode Klasifikasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Kode</label>
                    <input type="text" name="kode" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Uraian</label>
                    <textarea name="uraian" class="form-control" rows="3" required></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const tabelBody   = document.getElementById('tabelBody');
    const rows        = () => Array.from(tabelBody.querySelectorAll('tr'));

    // ==== SEARCH ====
    searchInput.addEventListener('keyup', function () {
        const keyword = this.value.toLowerCase();
        rows().forEach(row => {
            const kode   = row.children[1]?.textContent.toLowerCase() || '';
            const uraian = row.children[2]?.textContent.toLowerCase() || '';
            row.style.display = (kode.includes(keyword) || uraian.includes(keyword)) ? '' : 'none';
        });
    });

    // ==== SORT ====
    let sortState = {}; // {colIndex: 'asc' | 'desc'}

    document.querySelectorAll('.sortable').forEach(th => {
        th.addEventListener('click', function () {
            const col = parseInt(this.dataset.col);
            const currentDir = sortState[col] === 'asc' ? 'desc' : 'asc';
            sortState = { [col]: currentDir }; // reset kolom lain

            const sortedRows = rows().sort((a, b) => {
                const aText = a.children[col]?.textContent.trim().toLowerCase() || '';
                const bText = b.children[col]?.textContent.trim().toLowerCase() || '';
                if (aText < bText) return currentDir === 'asc' ? -1 : 1;
                if (aText > bText) return currentDir === 'asc' ? 1 : -1;
                return 0;
            });

            sortedRows.forEach(row => tabelBody.appendChild(row));

            // update ikon panah
            document.querySelectorAll('.sort-icon').forEach(icon => icon.textContent = '↕');
            this.querySelector('.sort-icon').textContent = currentDir === 'asc' ? '↑' : '↓';

            // update nomor urut kolom "No"
            rows().forEach((row, i) => {
                if (row.children[0]) row.children[0].textContent = i + 1;
            });
        });
    });
});
</script>
@endpush

@push('styles')
<style>
    .sortable {
        cursor: pointer;
        user-select: none;
        transition: background-color 0.15s ease;
    }
    .sortable:hover {
        background-color: #e9ecef;
    }
    .sort-icon {
        color: #adb5bd;
        font-size: 0.85rem;
        margin-left: 6px;
        transition: color 0.15s ease;
    }
    .sort-icon.active {
        color: #0d6efd;
    }
</style>
@endpush
@endsection