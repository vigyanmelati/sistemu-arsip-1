@extends('layouts.app')

@section('page-title', 'Pratinjau Disposisi')
@section('page-subtitle', 'Surat Masuk Agenda ' . ($surat->nomor_agenda ?: '-'))

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h5 class="mb-1"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Lembar Disposisi</h5>
                <small class="text-muted">{{ $surat->nomor_dokumen }} · {{ $surat->instansi_satker }}</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('surat-masuk.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
                <a href="{{ route('surat-masuk.show', $surat->id) }}" class="btn btn-outline-primary">
                    <i class="bi bi-eye me-1"></i> Lihat Detail
                </a>
                <button type="button" class="btn btn-success" id="printDisposition">
                    <i class="bi bi-printer me-1"></i> Cetak Disposisi
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="alert alert-info rounded-0 border-0 mb-0 py-2 px-3">
            <i class="bi bi-info-circle me-1"></i>
            Gunakan tombol <strong>Cetak Disposisi</strong> untuk mencetak. Setelah selesai, pilih <strong>Kembali ke Daftar</strong>.
        </div>
        <iframe id="dispositionFrame" src="{{ route('surat-masuk.disposisi.pdf', $surat->id) }}"
                title="Pratinjau lembar disposisi" class="w-100 border-0" style="height:72vh"></iframe>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('printDisposition').addEventListener('click', function () {
    const frame = document.getElementById('dispositionFrame');
    try {
        frame.contentWindow.focus();
        frame.contentWindow.print();
    } catch (error) {
        window.open(frame.src, '_blank', 'noopener');
    }
});
</script>
@endpush
