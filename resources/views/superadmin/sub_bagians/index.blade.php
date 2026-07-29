@extends('layouts.app')

@section('title', 'Master Sub Bagian')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Master Data Sub Bagian</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            + Tambah Sub Bagian
        </button>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body">

            {{-- SEARCH BOX --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari nama sub bagian...">
                </div>
            </div>

            <table class="table table-bordered table-striped" id="tabelSubBagian">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th class="sortable" data-col="1">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Nama Sub Bagian</span>
                                <span class="sort-icon"><i class="bi bi-arrow-down-up"></i></span>
                            </div>
                        </th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tabelBody">
                    @forelse ($subBagians as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->nama_sub_bagian }}</td>
                            <td>
                                <button class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEdit{{ $item->id }}">
                                    Edit
                                </button>

                                <form action="{{ route('superadmin.sub-bagians.destroy', $item->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin hapus sub bagian ini?')">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>

                        {{-- MODAL EDIT --}}
                        <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form method="POST"
                                      action="{{ route('superadmin.sub-bagians.update', $item->id) }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Sub Bagian</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Nama Sub Bagian</label>
                                                <input type="text"
                                                       name="nama_sub_bagian"
                                                       class="form-control"
                                                       value="{{ $item->nama_sub_bagian }}"
                                                       required>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button class="btn btn-primary">Update</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Data belum tersedia</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('superadmin.sub-bagians.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Sub Bagian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Sub Bagian</label>
                        <input type="text"
                               name="nama_sub_bagian"
                               class="form-control"
                               placeholder="Masukkan nama sub bagian"
                               required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const tabelBody   = document.getElementById('tabelBody');
    const rows        = () => Array.from(tabelBody.querySelectorAll('tr')).filter(r => r.children.length > 1);

    searchInput.addEventListener('keyup', function () {
        const keyword = this.value.toLowerCase();
        rows().forEach(row => {
            const nama = row.children[1]?.textContent.toLowerCase() || '';
            row.style.display = nama.includes(keyword) ? '' : 'none';
        });
    });

    let sortState = {};
    document.querySelectorAll('.sortable').forEach(th => {
        th.addEventListener('click', function () {
            const col = parseInt(this.dataset.col);
            const currentDir = sortState[col] === 'asc' ? 'desc' : 'asc';
            sortState = { [col]: currentDir };

            const sortedRows = rows().sort((a, b) => {
                const aText = a.children[col]?.textContent.trim().toLowerCase() || '';
                const bText = b.children[col]?.textContent.trim().toLowerCase() || '';
                if (aText < bText) return currentDir === 'asc' ? -1 : 1;
                if (aText > bText) return currentDir === 'asc' ? 1 : -1;
                return 0;
            });

            sortedRows.forEach(row => tabelBody.appendChild(row));

            document.querySelectorAll('.sort-icon').forEach(icon => {
                icon.classList.remove('active');
                icon.innerHTML = '<i class="bi bi-arrow-down-up"></i>';
            });

            const iconEl = this.querySelector('.sort-icon');
            iconEl.classList.add('active');
            iconEl.innerHTML = currentDir === 'asc'
                ? '<i class="bi bi-sort-alpha-down"></i>'
                : '<i class="bi bi-sort-alpha-down-alt"></i>';

            rows().forEach((row, i) => {
                if (row.children[0]) row.children[0].textContent = i + 1;
            });
        });
    });
});
</script>
@endpush
@endsection