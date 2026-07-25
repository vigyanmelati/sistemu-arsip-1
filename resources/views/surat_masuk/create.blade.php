@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h4>Tambah Surat Masuk</h4>
    </div>

    <div class="card-body">

        {{-- ALERT SUCCESS --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- ALERT ERROR --}}
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- VALIDATION ERROR --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Terjadi Kesalahan!</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('surat-masuk.store') }}"
              method="POST"
              enctype="multipart/form-data" id="formSuratMasuk">

            @csrf

            <div class="row">

                {{-- INSTANSI --}}
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">
                        Instansi/Satker*
                    </label>

                    <div class="position-relative" id="instansiCombobox">
                        <input type="text" id="instansiSearch" class="form-control" autocomplete="off"
                               placeholder="Ketik nama instansi/satker..." role="combobox"
                               aria-expanded="false" aria-controls="instansiSuggestions">
                        <input type="hidden" name="instansi_id" id="instansiId" value="{{ old('instansi_id') }}">
                        <div id="instansiSuggestions" class="list-group position-absolute w-100 shadow-sm d-none"
                             style="z-index:1050;max-height:260px;overflow-y:auto"></div>
                        <div id="instansiError" class="invalid-feedback d-none">Pilih salah satu Instansi/Satker dari daftar saran.</div>
                    </div>
                    <small class="text-muted">Ketik sebagian nama untuk melihat saran instansi/satker.</small>
                </div>

            </div>

            <div class="row">

                {{-- TANGGAL DOKUMEN --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        Tanggal Dokumen*
                    </label>

                    <input type="date"
                           name="tanggal_dokumen"
                           class="form-control"
                           value="{{ old('tanggal_dokumen') }}">
                </div>

                {{-- TANGGAL PENYELESAIAN --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        Tanggal Penyelesaian*
                    </label>

                    <input type="date"
                           name="tanggal_penyelesaian"
                           class="form-control"
                           value="{{ old('tanggal_penyelesaian') }}">
                </div>

            </div>

            <div class="row">

                {{-- NOMOR DOKUMEN --}}
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-bold">
                        Nomor Dokumen*
                    </label>

                    <input type="text"
                           name="nomor_dokumen"
                           class="form-control"
                           placeholder="Enter Text"
                           value="{{ old('nomor_dokumen') }}">
                </div>

                {{-- NOMOR AGENDA --}}
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">
                        Nomor Agenda*
                    </label>

                    <input type="text"
                           name="nomor_agenda"
                           class="form-control"
                           placeholder="Enter Text"
                           value="{{ old('nomor_agenda') }}">
                </div>

            </div>

            <div class="row">

                {{-- KEPADA --}}
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">
                        Kepada*
                    </label>

                    <input type="text"
                           name="kepada"
                           class="form-control"
                           placeholder="Enter Text"
                           value="{{ old('kepada') }}">
                </div>

            </div>

            <div class="row">

                {{-- PERIHAL --}}
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">
                        Perihal*
                    </label>

                    <textarea name="perihal"
                              class="form-control"
                              rows="3"
                              placeholder="Enter Text">{{ old('perihal') }}</textarea>
                </div>

            </div>

            <div class="row">

                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">Tujuan Disposisi</label>
                    <select name="tujuan_disposisi_ids[]" class="form-select" multiple size="5">
                        @foreach($tujuanDisposisis as $tujuan)
                            <option value="{{ $tujuan->id }}" @selected(in_array($tujuan->id, old('tujuan_disposisi_ids', [])))>{{ $tujuan->nama_tujuan }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Opsional. Tahan Ctrl (Windows) untuk memilih lebih dari satu tujuan.</small>
                </div>

                {{-- CATATAN --}}
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">
                        Catatan
                    </label>

                    <textarea name="catatan"
                              class="form-control"
                              rows="3"
                              placeholder="Enter Text">{{ old('catatan') }}</textarea>
                </div>

            </div>

            <div class="row">

                {{-- FILE --}}
                <div class="col-md-12 mb-4">
                    <label class="form-label fw-bold">
                        File Input
                    </label>

                    <input type="file"
                           name="file_input"
                           class="form-control">
                </div>

            </div>

            <button type="submit"
                    class="btn btn-primary" id="btnSubmitSuratMasuk">
                Submit
            </button>

            <button type="reset"
                    class="btn btn-secondary">
                Reset
            </button>

            <a href="{{ route('surat-masuk.index') }}"
               class="btn btn-danger">
                Cancel
            </a>

        </form>

    </div>
</div>

<div class="modal fade" id="quickInstansiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><form id="quickInstansiForm" class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Tambah Instansi/Satker</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div id="quickInstansiErrors" class="alert alert-danger d-none"></div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-bold">Nama Instansi*</label><input class="form-control" name="nama_instansi" id="quickNamaInstansi" required></div>
                <div class="col-md-6"><label class="form-label fw-bold">Alamat Kantor</label><textarea class="form-control" name="alamat" rows="2"></textarea></div>
                <div class="col-md-3"><label class="form-label">Nomor Telepon</label><input class="form-control" name="telepon" inputmode="numeric"></div>
                <div class="col-md-3"><label class="form-label">Nomor Fax</label><input class="form-control" name="fax" inputmode="numeric"></div>
                <div class="col-md-3"><label class="form-label">Alamat Email</label><input type="email" class="form-control" name="email"></div>
                <div class="col-md-3"><label class="form-label">Alamat Web</label><input type="url" class="form-control" name="website" placeholder="https://..."></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="quickInstansiSubmit">Simpan dan Pilih</button></div>
    </form></div>
</div>
@push('scripts')
<script>
const instansiData = @json($instansis->map(fn ($item) => ['id' => $item->id, 'nama' => $item->nama_instansi])->values());
const instansiSearch = document.getElementById('instansiSearch');
const instansiId = document.getElementById('instansiId');
const suggestions = document.getElementById('instansiSuggestions');
const oldInstansi = instansiData.find(item => String(item.id) === String(instansiId.value));
if (oldInstansi) instansiSearch.value = oldInstansi.nama;

function chooseInstansi(item) {
    instansiId.value = item.id;
    instansiSearch.value = item.nama;
    instansiSearch.classList.remove('is-invalid');
    document.getElementById('instansiError').classList.add('d-none');
    suggestions.classList.add('d-none');
    instansiSearch.setAttribute('aria-expanded', 'false');
}

function showSuggestions() {
    const keyword = instansiSearch.value.trim().toLocaleLowerCase('id-ID');
    const matches = instansiData.filter(item => item.nama.toLocaleLowerCase('id-ID').includes(keyword)).slice(0, 12);
    suggestions.replaceChildren();
    matches.forEach(item => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'list-group-item list-group-item-action';
        button.textContent = item.nama;
        button.addEventListener('click', () => chooseInstansi(item));
        suggestions.appendChild(button);
    });
    if (!matches.length && keyword) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'list-group-item list-group-item-action text-primary fw-semibold';
        button.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Tambah Instansi/Satker “<span></span>”';
        button.querySelector('span').textContent = instansiSearch.value.trim();
        button.addEventListener('click', () => {
            document.getElementById('quickNamaInstansi').value = instansiSearch.value.trim();
            suggestions.classList.add('d-none');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('quickInstansiModal')).show();
        });
        suggestions.appendChild(button);
    }
    suggestions.classList.toggle('d-none', !keyword && !matches.length);
    instansiSearch.setAttribute('aria-expanded', suggestions.classList.contains('d-none') ? 'false' : 'true');
}

instansiSearch.addEventListener('input', () => { instansiId.value = ''; showSuggestions(); });
instansiSearch.addEventListener('focus', showSuggestions);
document.addEventListener('click', event => { if (!document.getElementById('instansiCombobox').contains(event.target)) suggestions.classList.add('d-none'); });

document.getElementById('quickInstansiForm').addEventListener('submit', async function (event) {
    event.preventDefault();
    const submit = document.getElementById('quickInstansiSubmit');
    const errorBox = document.getElementById('quickInstansiErrors');
    submit.disabled = true;
    errorBox.classList.add('d-none');
    try {
        const response = await fetch('{{ route('surat-instansi.quick-store') }}', {
            method: 'POST', headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: new FormData(this)
        });
        const result = await response.json();
        if (!response.ok) throw result;
        const item = {id: result.instansi.id, nama: result.instansi.nama_instansi};
        instansiData.push(item);
        chooseInstansi(item);
        bootstrap.Modal.getInstance(document.getElementById('quickInstansiModal')).hide();
        this.reset();
    } catch (error) {
        const messages = error.errors ? Object.values(error.errors).flat() : [error.message || 'Instansi gagal disimpan.'];
        errorBox.innerHTML = '';
        messages.forEach(message => { const div = document.createElement('div'); div.textContent = message; errorBox.appendChild(div); });
        errorBox.classList.remove('d-none');
    } finally { submit.disabled = false; }
});

document.getElementById('formSuratMasuk').addEventListener('submit', function (e) {
    if (!instansiId.value) {
        e.preventDefault();
        instansiSearch.classList.add('is-invalid');
        document.getElementById('instansiError').classList.remove('d-none');
        instansiSearch.focus();
        return;
    }
    const btn = document.getElementById('btnSubmitSuratMasuk');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
});
</script>
@endpush
@endsection
