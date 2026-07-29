@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Manajemen User</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            + Tambah User
        </button>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body">

            {{-- SEARCH BOX --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari nama / email...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="tabelUser">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th class="sortable" data-col="1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Nama</span>
                                    <span class="sort-icon"><i class="bi bi-arrow-down-up"></i></span>
                                </div>
                            </th>
                            <th class="sortable" data-col="2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Email</span>
                                    <span class="sort-icon"><i class="bi bi-arrow-down-up"></i></span>
                                </div>
                            </th>
                            <th width="15%" class="sortable" data-col="3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Role</span>
                                    <span class="sort-icon"><i class="bi bi-arrow-down-up"></i></span>
                                </div>
                            </th>
                            <th width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabelBody">
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $users->firstItem() + $loop->index }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                  @php
                                    $badge = match($user->role){
                                        'super_admin' => 'danger',
                                        'admin' => 'primary',
                                        'tu' => 'warning',
                                        'user' => 'secondary',
                                        default => 'secondary',
                                    };

                                    $role = match($user->role){
                                        'super_admin' => 'Super Admin',
                                        'admin' => 'Admin',
                                        'tu' => 'Tata Usaha',
                                        'user' => 'User',
                                        default => ucfirst($user->role),
                                    };
                                @endphp

                                <span class="badge bg-{{ $badge }}">
                                    {{ $role }}
                                </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal{{ $user->id }}">
                                        Edit
                                    </button>

                                    @if(auth()->id() !== $user->id)
                                    <form action="{{ route('superadmin.users.destroy', $user->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin hapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            Hapus
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>

                            {{-- MODAL EDIT --}}
                            <div class="modal fade" id="editModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">

                                        <form method="POST"
                                              action="{{ route('superadmin.users.update', $user->id) }}">
                                            @csrf
                                            @method('PUT')

                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit User</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Nama</label>
                                                    <input type="text" name="name"
                                                           class="form-control"
                                                           value="{{ $user->name }}" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" name="email"
                                                           class="form-control"
                                                           value="{{ $user->email }}" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Role</label>
                                                    <select name="role" class="form-select" required>
                                                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                                        <option value="tu" {{ $user->role == 'tu' ? 'selected' : '' }}>Tata Usaha</option>
                                                        <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3 d-none subBagianWrapper">
                                                    <label class="form-label">Sub Bagian</label>
                                                    <select name="sub_bagian_id" class="form-select">
                                                        <option value="">-- Pilih Sub Bagian --</option>
                                                        @foreach($subBagians as $sb)
                                                            <option value="{{ $sb->id }}"
                                                                {{ $user->sub_bagian_id == $sb->id ? 'selected' : '' }}>
                                                                {{ $sb->nama_sub_bagian }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Password (opsional)</label>
                                                    <input type="password" name="password"
                                                           class="form-control"
                                                           placeholder="Kosongkan jika tidak diubah">
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
                                <td colspan="5" class="text-center">Data user belum tersedia</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST" action="{{ route('superadmin.users.store') }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                       <select name="role" class="form-select" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin">Admin</option>
                            <option value="tu">Tata Usaha</option>
                            <option value="user">User</option>
                        </select>
                                            </div>

                    <div class="mb-3 d-none" id="subBagianWrapper">
                        <label class="form-label">Sub Bagian</label>
                        <select name="sub_bagian_id" class="form-select">
                            <option value="">-- Pilih Sub Bagian --</option>
                            @foreach($subBagians as $sb)
                                <option value="{{ $sb->id }}">{{ $sb->nama_sub_bagian }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary">Simpan</button>
                </div>
            </form>

        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {

    function toggleSubBagian(roleSelect, wrapper) {
        if (roleSelect.value === 'user') {
            wrapper.classList.remove('d-none');
        } else {
            wrapper.classList.add('d-none');
            wrapper.querySelector('select').value = '';
        }
    }

    // Modal Tambah
    const roleTambah = document.querySelector('#modalTambah select[name="role"]');
    const wrapperTambah = document.getElementById('subBagianWrapper');

    roleTambah.addEventListener('change', () => {
        toggleSubBagian(roleTambah, wrapperTambah);
    });

    // Modal Edit (multiple)
    document.querySelectorAll('[id^="editModal"]').forEach(modal => {
        const roleEdit = modal.querySelector('select[name="role"]');
        const wrapperEdit = modal.querySelector('.subBagianWrapper');

        toggleSubBagian(roleEdit, wrapperEdit);

        roleEdit.addEventListener('change', () => {
            toggleSubBagian(roleEdit, wrapperEdit);
        });
    });

    // ==== SEARCH & SORT ====
    const searchInput = document.getElementById('searchInput');
    const tabelBody   = document.getElementById('tabelBody');
    const rows        = () => Array.from(tabelBody.querySelectorAll('tr')).filter(r => r.children.length > 1);

    searchInput.addEventListener('keyup', function () {
        const keyword = this.value.toLowerCase();
        rows().forEach(row => {
            const nama  = row.children[1]?.textContent.toLowerCase() || '';
            const email = row.children[2]?.textContent.toLowerCase() || '';
            row.style.display = (nama.includes(keyword) || email.includes(keyword)) ? '' : 'none';
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

@endsection