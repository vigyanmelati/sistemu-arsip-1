@extends('layouts.app')

@section('title', 'Import Data SINAR V1')
@section('page-title', 'Import SINAR V1')
@section('page-subtitle', 'Pilih lampiran, hubungkan database lama, lalu migrasikan secara bertahap')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div id="importAlert"></div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card h-100 shadow-sm"><div class="card-body"><small class="text-muted">Lampiran di staging</small><h4 id="stagedCount" class="mt-2">{{ number_format($stagedFiles->count(),0,',','.') }} file</h4><span id="stagedSize" class="text-muted">{{ number_format($stagedBytes / 1048576,2,',','.') }} MB</span></div></div></div>
    <div class="col-md-4"><div class="card h-100 shadow-sm"><div class="card-body"><small class="text-muted">Dokumen historis V2</small><h4 class="mt-2">{{ number_format($stats->sum(),0,',','.') }}</h4><span class="text-muted">Data yang sudah pernah diimpor</span></div></div></div>
    <div class="col-md-4"><div class="card h-100 shadow-sm"><div class="card-body"><small class="text-muted">Status lampiran hasil import</small><div class="mt-2">Tersedia: <strong>{{ number_format($stats['TERSEDIA'] ?? 0,0,',','.') }}</strong></div><div>Hilang: <strong>{{ number_format($stats['HILANG'] ?? 0,0,',','.') }}</strong></div></div></div></div>
</div>

<div class="row g-4">
<div class="col-lg-7">
    <div class="card shadow-sm mb-4">
        <div class="card-header"><strong>1. Pilih folder lampiran SINAR V1</strong></div>
        <div class="card-body">
            <p>Pilih folder <code>public/uploads</code> atau langsung folder <code>uploads</code> dari backup SINAR V1. Struktur subfolder kategori akan dipertahankan.</p>
            <input type="file" id="folderInput" class="form-control mb-3" webkitdirectory directory multiple>
            <div class="progress mb-2 d-none" id="uploadProgress" style="height:22px"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%">0%</div></div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" id="uploadFolderButton" disabled><i class="bi bi-folder2-open"></i> Unggah Folder ke Staging</button>
                <form method="POST" action="{{ route('sinar-v1.import.stage-files.clear') }}" onsubmit="return confirm('Kosongkan seluruh staging lampiran Anda?')">@csrf @method('DELETE')<button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Kosongkan Staging</button></form>
            </div>
            <div id="folderSummary" class="small text-muted mt-3">Belum ada folder baru yang dipilih.</div>
            <div class="alert alert-secondary mt-3 mb-0"><i class="bi bi-shield-lock"></i> File diunggah bertahap ke storage privat. Ekstensi executable ditolak dan path absolut komputer tidak disimpan.</div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header"><strong>2. Koneksi database SINAR V1</strong></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8"><label class="form-label">Host</label><input name="db_host" form="importForm" class="form-control" value="127.0.0.1" required autocomplete="off"></div>
                <div class="col-md-4"><label class="form-label">Port</label><input name="db_port" form="importForm" type="number" class="form-control" value="3306" min="1" max="65535" required></div>
                <div class="col-md-12"><label class="form-label">Nama database</label><input name="db_database" form="importForm" class="form-control" placeholder="siarsip" required autocomplete="off"></div>
                <div class="col-md-6"><label class="form-label">Username</label><input name="db_username" form="importForm" class="form-control" required autocomplete="off"></div>
                <div class="col-md-6"><label class="form-label">Password</label><div class="input-group"><input name="db_password" form="importForm" id="dbPassword" type="password" class="form-control" autocomplete="new-password"><button type="button" class="btn btn-outline-secondary" id="togglePassword"><i class="bi bi-eye"></i></button></div></div>
            </div>
            <small class="text-muted d-block mt-3"><i class="bi bi-info-circle"></i> Kredensial hanya digunakan selama request dry-run/import. Password tidak disimpan ke database, session, atau file <code>.env</code>.</small>
        </div>
    </div>
</div>

<div class="col-lg-5">
    <div class="card shadow-sm sticky-lg-top" style="top:1rem">
        <div class="card-header"><strong>3. Periksa dan import</strong></div>
        <div class="card-body">
            <form id="importForm" method="POST" action="{{ route('sinar-v1.import.run') }}">@csrf
                <input type="hidden" name="mode" id="importMode" value="dry-run">
                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="skip_files" value="1" id="skipFiles"><label class="form-check-label" for="skipFiles">Import data tanpa menyalin lampiran</label></div>
                <button type="button" class="btn btn-outline-primary w-100 mb-3" id="dryRunButton"><i class="bi bi-search"></i> Tes Koneksi &amp; Dry-run</button>
                <div id="processOutput" class="d-none mb-3"><label class="form-label">Hasil proses</label><pre class="bg-dark text-light p-3 rounded" style="white-space:pre-wrap;max-height:300px;overflow:auto"></pre></div>
                <hr>
                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="confirmation" value="1" id="confirmation"><label class="form-check-label" for="confirmation">Saya sudah membuat backup dan memahami proses ini.</label></div>
                <button type="button" class="btn btn-primary w-100" id="commitButton"><i class="bi bi-database-up"></i> Import ke SINAR V2</button>
            </form>
            <p class="small text-muted mt-3 mb-0">Import bersifat idempotent: dokumen yang sama diperbarui, bukan digandakan. File sumber dan staging tidak dihapus otomatis.</p>
        </div>
    </div>
    <a href="{{ route('sinar-v1.index') }}" class="btn btn-link w-100 mt-3">Kembali ke dokumen historis</a>
</div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const folderInput = document.getElementById('folderInput');
    const uploadButton = document.getElementById('uploadFolderButton');
    const folderSummary = document.getElementById('folderSummary');
    const progress = document.getElementById('uploadProgress');
    const progressBar = progress.querySelector('.progress-bar');
    const importForm = document.getElementById('importForm');
    const outputBox = document.getElementById('processOutput');
    const alertBox = document.getElementById('importAlert');
    let selectedFiles = [];

    const bytes = value => `${(value / 1048576).toLocaleString('id-ID', {maximumFractionDigits: 2})} MB`;
    const alert = (message, type = 'info') => alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;

    folderInput.addEventListener('change', () => {
        selectedFiles = Array.from(folderInput.files).filter(file => /(^|\/)uploads\//i.test(file.webkitRelativePath));
        const total = selectedFiles.reduce((sum, file) => sum + file.size, 0);
        folderSummary.textContent = `${selectedFiles.length.toLocaleString('id-ID')} file valid dipilih (${bytes(total)}).`;
        uploadButton.disabled = selectedFiles.length === 0;
        if (!selectedFiles.length) alert('Folder yang dipilih harus mengandung folder uploads.', 'warning');
    });

    uploadButton.addEventListener('click', async () => {
        uploadButton.disabled = true;
        progress.classList.remove('d-none');
        let uploaded = 0;
        const rejected = [];
        try {
            for (let offset = 0; offset < selectedFiles.length; offset += 15) {
                const batch = selectedFiles.slice(offset, offset + 15);
                const body = new FormData();
                batch.forEach(file => { body.append('files[]', file); body.append('paths[]', file.webkitRelativePath); });
                const response = await fetch('{{ route('sinar-v1.import.stage-files') }}', {method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body});
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Upload batch gagal.');
                uploaded += result.stored;
                rejected.push(...result.rejected);
                const percent = Math.round(Math.min(offset + batch.length, selectedFiles.length) / selectedFiles.length * 100);
                progressBar.style.width = `${percent}%`; progressBar.textContent = `${percent}%`;
                document.getElementById('stagedCount').textContent = `${result.total_files.toLocaleString('id-ID')} file`;
                document.getElementById('stagedSize').textContent = bytes(result.total_bytes);
            }
            alert(`${uploaded.toLocaleString('id-ID')} file masuk staging.${rejected.length ? ` ${rejected.length} file ditolak karena tipe tidak diizinkan.` : ''}`, rejected.length ? 'warning' : 'success');
        } catch (error) { alert(error.message, 'danger'); }
        finally { uploadButton.disabled = false; progressBar.classList.remove('progress-bar-animated'); }
    });

    async function run(mode) {
        if (!importForm.reportValidity()) return;
        if (mode === 'commit' && !document.getElementById('confirmation').checked) { alert('Konfirmasi backup wajib dicentang sebelum import.', 'warning'); return; }
        if (mode === 'commit' && !confirm('Mulai import SINAR V1 sekarang?')) return;
        document.getElementById('importMode').value = mode;
        const button = mode === 'commit' ? document.getElementById('commitButton') : document.getElementById('dryRunButton');
        button.disabled = true; const oldHtml = button.innerHTML; button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';
        try {
            const response = await fetch(importForm.action, {method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body:new FormData(importForm)});
            const result = await response.json();
            outputBox.classList.remove('d-none'); outputBox.querySelector('pre').textContent = result.output || result.message || 'Tidak ada keluaran.';
            if (!response.ok) throw new Error(result.message || 'Proses gagal.');
            alert(mode === 'commit' ? 'Import selesai. Muat ulang halaman untuk melihat statistik terbaru.' : 'Koneksi berhasil dan dry-run selesai.', 'success');
        } catch (error) { alert(error.message, 'danger'); }
        finally { button.disabled = false; button.innerHTML = oldHtml; }
    }

    document.getElementById('dryRunButton').addEventListener('click', () => run('dry-run'));
    document.getElementById('commitButton').addEventListener('click', () => run('commit'));
    document.getElementById('togglePassword').addEventListener('click', () => { const field=document.getElementById('dbPassword'); field.type=field.type==='password'?'text':'password'; });
})();
</script>
@endpush
