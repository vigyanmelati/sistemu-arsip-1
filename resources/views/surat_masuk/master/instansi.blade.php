@extends('layouts.app')
@section('page-title', 'Instansi/Satker Asal Surat')
@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Tambah Instansi/Satker Asal Surat</h5>
        <a href="{{ route('surat-instansi.duplicates') }}" class="btn btn-warning"><i class="bi bi-files"></i> Cek Duplikat Nama</a>
    </div>
    <div class="card-body">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('surat-instansi.store') }}">@csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-bold">Nama Instansi*</label><input class="form-control" name="nama_instansi" value="{{ old('nama_instansi') }}" placeholder="Enter Text" required></div>
                <div class="col-md-6"><label class="form-label fw-bold">Alamat Kantor</label><textarea class="form-control" name="alamat" rows="2" placeholder="Enter Text">{{ old('alamat') }}</textarea></div>
                <div class="col-md-3"><label class="form-label fw-bold">Nomor Telepon</label><input class="form-control" name="telepon" value="{{ old('telepon') }}" inputmode="numeric" placeholder="Enter Text"></div>
                <div class="col-md-3"><label class="form-label fw-bold">Nomor Fax</label><input class="form-control" name="fax" value="{{ old('fax') }}" inputmode="numeric" placeholder="Enter Text"></div>
                <div class="col-md-3"><label class="form-label fw-bold">Alamat Email</label><input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="user@domain.com"></div>
                <div class="col-md-3"><label class="form-label fw-bold">Alamat Web</label><input type="url" class="form-control" name="website" value="{{ old('website') }}" placeholder="https://domain.com"></div>
            </div>
            <div class="mt-3"><button class="btn btn-primary"><i class="bi bi-plus-circle"></i> Simpan</button><button type="reset" class="btn btn-secondary">Reset</button></div>
        </form>
    </div>
</div>

<div class="card"><div class="card-header"><h5 class="mb-0">Daftar Instansi/Satker</h5></div><div class="card-body">
    <form method="GET" action="{{ route('surat-instansi.index') }}" class="row g-2 mb-3">
        <div class="col-md-8 col-lg-6">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="search" name="search" class="form-control" value="{{ $search }}"
                       placeholder="Cari nama, alamat, telepon, email, atau website..." autofocus>
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </div>
        @if($search !== '')
            <div class="col-auto"><a href="{{ route('surat-instansi.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Hapus Pencarian</a></div>
        @endif
    </form>
    @if($search !== '')
        <p class="text-muted small">Ditemukan <strong>{{ $items->total() }}</strong> instansi untuk pencarian “{{ $search }}”.</p>
    @endif
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Instansi</th><th>Kontak</th><th>Status</th><th style="min-width:480px">Perbarui</th></tr></thead><tbody>
    @forelse($items as $item)<tr>
        <td><strong>{{ $item->nama_instansi }}</strong><small class="d-block text-muted">{{ $item->alamat ?: '-' }}</small></td>
        <td><small>Telp: {{ $item->telepon ?: '-' }} · Fax: {{ $item->fax ?: '-' }}<br>{{ $item->email ?: '-' }} · {{ $item->website ?: '-' }}</small></td>
        <td><span class="badge bg-{{ $item->aktif ? 'success' : 'secondary' }}">{{ $item->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
        <td><form method="POST" action="{{ route('surat-instansi.update', $item) }}" class="row g-1">@csrf @method('PUT')
            <div class="col-6"><input class="form-control form-control-sm" name="nama_instansi" value="{{ $item->nama_instansi }}" title="Nama Instansi" required></div>
            <div class="col-6"><input class="form-control form-control-sm" name="alamat" value="{{ $item->alamat }}" placeholder="Alamat Kantor"></div>
            <div class="col-3"><input class="form-control form-control-sm" name="telepon" value="{{ $item->telepon }}" placeholder="Telepon"></div>
            <div class="col-3"><input class="form-control form-control-sm" name="fax" value="{{ $item->fax }}" placeholder="Fax"></div>
            <div class="col-3"><input type="email" class="form-control form-control-sm" name="email" value="{{ $item->email }}" placeholder="Email"></div>
            <div class="col-3"><input type="url" class="form-control form-control-sm" name="website" value="{{ $item->website }}" placeholder="Website"></div>
            <div class="col-12 d-flex justify-content-end align-items-center gap-2"><label><input type="checkbox" name="aktif" value="1" @checked($item->aktif)> Aktif</label><button class="btn btn-sm btn-outline-primary">Simpan Perubahan</button></div>
        </form></td>
    </tr>@empty<tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>@endforelse
    </tbody></table></div>{{ $items->links() }}
</div></div>
@endsection
