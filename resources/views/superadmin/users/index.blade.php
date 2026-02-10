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
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th width="15%">Role</th>
                        <th width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->role === 'superadmin' ? 'danger' : 'secondary' }}">
                                    {{ ucfirst($user->role) }}
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
                                                    <!-- <option value="superadmin" {{ $user->role == 'superadmin' ? 'selected' : '' }}>Superadmin</option> -->
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
                            <!-- <option value="superadmin">Superadmin</option> -->
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
});
</script>

@endsection
