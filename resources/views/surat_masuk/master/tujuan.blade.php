@extends('layouts.app')
@section('page-title', 'Tujuan Disposisi')
@section('content')
<div class="card"><div class="card-body">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('tujuan-disposisi.store') }}" class="row g-2 mb-4">@csrf
        <div class="col-md-9"><label class="form-label fw-bold">Nama Tujuan Disposisi*</label><input class="form-control" name="nama_tujuan" value="{{ old('nama_tujuan') }}" required></div>
        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100"><i class="bi bi-plus-circle"></i> Tambahkan</button></div>
    </form>
    <div class="table-responsive"><table class="table table-hover"><thead><tr><th>Tujuan Disposisi</th><th>Status</th><th style="width:340px">Perbarui</th></tr></thead><tbody>
    @forelse($items as $item)<tr><td>{{ $item->nama_tujuan }}</td><td><span class="badge bg-{{ $item->aktif ? 'success' : 'secondary' }}">{{ $item->aktif ? 'Aktif' : 'Nonaktif' }}</span></td><td>
        <form method="POST" action="{{ route('tujuan-disposisi.update', $item) }}" class="d-flex gap-2">@csrf @method('PUT')
            <input class="form-control form-control-sm" name="nama_tujuan" value="{{ $item->nama_tujuan }}" required><label class="d-flex align-items-center gap-1"><input type="checkbox" name="aktif" value="1" @checked($item->aktif)> Aktif</label><button class="btn btn-sm btn-outline-primary">Simpan</button>
        </form></td></tr>@empty<tr><td colspan="3" class="text-center text-muted">Belum ada data.</td></tr>@endforelse
    </tbody></table></div>{{ $items->links() }}
</div></div>
@endsection
