@extends('layouts.app')
@section('page-title', 'Cek Duplikat Instansi/Satker')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h4 class="mb-1">Cek Duplikat Nama Instansi</h4><p class="text-muted mb-0">Pemeriksaan mengabaikan perbedaan huruf, spasi, dan tanda baca.</p></div>
    <a href="{{ route('surat-instansi.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

@forelse($groups as $index => $items)
<div class="card mb-3 border-warning">
    <div class="card-header bg-warning bg-opacity-10"><strong>Kelompok Duplikat {{ $index + 1 }}</strong> <span class="badge bg-warning text-dark">{{ $items->count() }} data</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('surat-instansi.merge-duplicates') }}" onsubmit="return confirm('Gabungkan data duplikat ini? Seluruh Surat Masuk akan dialihkan ke data utama yang dipilih.');">@csrf
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Data Utama</th><th>Nama Instansi</th><th>Kontak</th><th>Surat Terkait</th></tr></thead><tbody>
            @foreach($items as $item)
                <tr>
                    <td><input class="form-check-input" type="radio" name="canonical_id" value="{{ $item->id }}" @checked($loop->first) required></td>
                    <td><strong>{{ $item->nama_instansi }}</strong><small class="d-block text-muted">{{ $item->alamat ?: 'Alamat belum diisi' }}</small></td>
                    <td><small>Telp: {{ $item->telepon ?: '-' }} · Fax: {{ $item->fax ?: '-' }}<br>{{ $item->email ?: '-' }} · {{ $item->website ?: '-' }}</small></td>
                    <td><span class="badge bg-primary">{{ $item->surat_masuks_count }}</span></td>
                </tr>
            @endforeach
            </tbody></table></div>
            @foreach($items as $item)<input type="hidden" name="all_ids[]" value="{{ $item->id }}">@endforeach
            <div class="text-end"><button type="submit" class="btn btn-warning merge-duplicates-btn"><i class="bi bi-intersect"></i> Gabungkan ke Data Utama</button></div>
        </form>
    </div>
</div>
@empty
<div class="card"><div class="card-body text-center py-5"><i class="bi bi-check-circle text-success display-4"></i><h5 class="mt-3">Tidak ditemukan nama instansi duplikat</h5><p class="text-muted">Seluruh nama Instansi/Satker saat ini sudah unik.</p></div></div>
@endforelse

@push('scripts')
<script>
document.querySelectorAll('form').forEach(form => form.addEventListener('submit', function () {
    this.querySelectorAll('input[name="duplicate_ids[]"]').forEach(input => input.remove());
    const canonical = this.querySelector('input[name="canonical_id"]:checked').value;
    this.querySelectorAll('input[name="all_ids[]"]').forEach(input => {
        if (input.value !== canonical) {
            const duplicate = document.createElement('input');
            duplicate.type = 'hidden'; duplicate.name = 'duplicate_ids[]'; duplicate.value = input.value;
            this.appendChild(duplicate);
        }
    });
}));
</script>
@endpush
@endsection
