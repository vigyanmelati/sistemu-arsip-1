@extends('layouts.app')

@section('title', 'Sidang Penilaian Pemusnahan Arsip')

@section('content')
<div class="container-fluid">

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif


    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Sidang & Persetujuan ANRI Pemusnahan Arsip</h4>
            <small class="text-muted">
                Tahun {{ $pemusnahan->tahun }} · Revisi & Persetujuan Dilakukan di Halaman Ini
            </small>

        </div>

        <div class="d-flex gap-2">
      <a href="{{ route('pemusnahan.export.usul', $pemusnahan->id) }}"
   class="btn btn-success">
    📥 Export Daftar Arsip
</a>

            <a href="{{ route('pemusnahan.usulan.show', $pemusnahan->id) }}"
            class="btn btn-secondary">
                ⬅ Kembali
            </a>
        </div>
    </div>


    {{-- ================= INFO ================= --}}
    <div class="alert alert-warning">
        <ul class="mb-0">
            <li>Halaman ini digunakan untuk <b>sidang penilaian sekaligus tindak lanjut ANRI</b></li>
            <li>Jika terdapat <b>revisi dari ANRI</b>, data arsip dapat <b>langsung diperbaiki di halaman ini</b></li>
            <li>Perubahan data <b>tersimpan otomatis</b> saat diedit</li>
            <li>Setelah <b>Surat Persetujuan Pemusnahan dari ANRI diterima</b>, silakan tekan tombol <b>“Disetujui ANRI”</b></li>
            <li>Minimal <b>1 arsip</b> harus diputuskan <b>Musnah</b></li>

        </ul>
    </div>

    {{-- ================= TABEL SIDANG ================= --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <strong>Daftar Arsip</strong>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>No</th>
                        <th>Uraian Arsip</th>
                        <th>Tahun</th>
                        <th>Jumlah</th>
                        <th>Tingkat</th>
                        <th>Aktif</th>
                        <th>Inaktif</th>
                        <th>Dokumen</th>
                        <th>Keputusan</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

                @foreach ($pemusnahan->details as $i => $detail)
                <tr>
                    <td class="text-center">{{ $i+1 }}</td>

                    {{-- URAIAN --}}
                    <td>
                        <span class="inline-text" onclick="editInline(this)">
                            {{ $detail->arsip->uraian_arsip }}
                        </span>
                        <input type="text"
                               class="form-control form-control-sm d-none inline-input"
                               data-model="arsip"
                               data-id="{{ $detail->id }}"
                               data-field="uraian_arsip"
                               value="{{ $detail->arsip->uraian_arsip }}"
                               onblur="saveInline(this)">
                    </td>

                    {{-- TAHUN --}}
                    <td class="text-center">
                        <span class="inline-text" onclick="editInline(this)">
                            {{ $detail->arsip->tahun_arsip }}
                        </span>
                        <input type="number"
                               class="form-control form-control-sm d-none inline-input"
                               data-model="arsip"
                               data-id="{{ $detail->id }}"
                               data-field="tahun_arsip"
                               value="{{ $detail->arsip->tahun_arsip }}"
                               onblur="saveInline(this)">
                    </td>

                    {{-- JUMLAH --}}
                    <td>
                        <span class="inline-text" onclick="editInline(this)">
                            {{ $detail->arsip->jumlah_berkas }} {{ $detail->arsip->satuan_arsip }}
                        </span>
                        <div class="d-flex gap-1 d-none inline-input">
                            <input type="number"
                                   class="form-control form-control-sm"
                                   data-model="arsip"
                                   data-id="{{ $detail->id }}"
                                   data-field="jumlah_berkas"
                                   value="{{ $detail->arsip->jumlah_berkas }}"
                                   onblur="saveInline(this)">
                            <input type="text"
                                   class="form-control form-control-sm"
                                   data-model="arsip"
                                   data-id="{{ $detail->id }}"
                                   data-field="satuan_arsip"
                                   value="{{ $detail->arsip->satuan_arsip }}"
                                   onblur="saveInline(this)">
                        </div>
                    </td>

                    {{-- TINGKAT PERKEMBANGAN --}}
                    <td>
                        <span class="inline-text" onclick="editInline(this)">
                            {{ ucfirst($detail->arsip->tingkat_perkembangan) ?? '-' }}
                        </span>
                        <select class="form-select form-select-sm d-none inline-input"
                                data-model="arsip"
                                data-id="{{ $detail->id }}"
                                data-field="tingkat_perkembangan"
                                onchange="saveInline(this)">
                            <option value="asli" {{ $detail->arsip->tingkat_perkembangan=='asli'?'selected':'' }}>Asli</option>
                            <option value="copy" {{ $detail->arsip->tingkat_perkembangan=='copy'?'selected':'' }}>Copy</option>
                            <option value="tembusan" {{ $detail->arsip->tingkat_perkembangan=='tembusan'?'selected':'' }}>Tembusan</option>
                        </select>
                    </td>

                    {{-- AKTIF TAHUN --}}
                    <td class="text-center">
                        <span class="inline-text" onclick="editInline(this)">
                            {{ $detail->arsip->aktif_tahun ?? '-' }}
                        </span>
                        <input type="number"
                            class="form-control form-control-sm d-none inline-input"
                            data-model="arsip"
                            data-id="{{ $detail->id }}"
                            data-field="aktif_tahun"
                            value="{{ $detail->arsip->aktif_tahun }}"
                            onblur="saveInline(this)">
                    </td>

                    {{-- INAKTIF TAHUN --}}
                    <td class="text-center">
                        <span class="inline-text" onclick="editInline(this)">
                            {{ $detail->arsip->inaktif_tahun ?? '-' }}
                        </span>
                        <input type="number"
                            class="form-control form-control-sm d-none inline-input"
                            data-model="arsip"
                            data-id="{{ $detail->id }}"
                            data-field="inaktif_tahun"
                            value="{{ $detail->arsip->inaktif_tahun }}"
                            onblur="saveInline(this)">
                    </td>

                    {{-- DOKUMEN --}}
                    <td class="text-center">
                        @if($detail->arsip->file_dokumen)
                            <a href="{{ asset('storage/'.$detail->arsip->file_dokumen) }}"
                            target="_blank"
                            class="btn btn-sm btn-outline-primary">
                                📄 Lihat
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    {{-- KEPUTUSAN --}}
                    <td>
                        <span class="inline-text" onclick="editInline(this)">
                            {{ ucfirst(str_replace('_',' ',$detail->keputusan)) ?: '-' }}
                        </span>
                        <select class="form-select form-select-sm d-none inline-input"
                                data-model="detail"
                                data-id="{{ $detail->id }}"
                                data-field="keputusan"
                                onchange="saveInline(this)">
                            <option value="">-- Pilih --</option>
                            <option value="musnah" {{ $detail->keputusan=='musnah'?'selected':'' }}>Musnah</option>
                            <option value="inaktif" {{ $detail->keputusan=='inaktif'?'selected':'' }}>Inaktif</option>
                            <option value="permanen" {{ $detail->keputusan=='permanen'?'selected':'' }}>
                                Permanen
                            </option>
                        </select>
                    </td>

                    {{-- CATATAN --}}
                    <td>
                        <span class="inline-text" onclick="editInline(this)">
                            {{ $detail->catatan ?: '-' }}
                        </span>
                        <textarea class="form-control form-control-sm d-none inline-input"
                                  rows="2"
                                  data-model="detail"
                                  data-id="{{ $detail->id }}"
                                  data-field="catatan"
                                  onblur="saveInline(this)">{{ $detail->catatan }}</textarea>
                    </td>

                    {{-- AKSI --}}
                    <td class="text-center">
                       <a href="{{ route('arsip.show', [
                            'arsip' => $detail->arsip->id,
                            'return' => url()->current()
                        ]) }}"
                        class="btn btn-sm btn-info">
                            🔍 Detail
                        </a>

                    </td>
                </tr>
                @endforeach

                </tbody>
            </table>
        </div>
    </div>

    {{-- ================= FINALISASI ================= --}}
<form action="{{ route('pemusnahan.anri.setujui', $pemusnahan->id) }}" 
      method="POST" 
      enctype="multipart/form-data">

    @csrf

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="mb-3">
                <label class="form-label">
                    Upload Surat Persetujuan ANRI <span class="text-danger">*</span>
                </label>
                <input type="file" 
                       name="file_persetujuan_anri" 
                       class="form-control"
                       accept="application/pdf"
                       required>
                <small class="text-muted">Format PDF, maksimal 2MB</small>
            </div>

            <div class="text-end">
                <button type="submit"
                    class="btn btn-danger"
                    onclick="return confirm(
                        'Tetapkan hasil sidang dan setujui pemusnahan arsip?\n\n' +
                        'Pastikan:\n' +
                        '- Revisi ANRI sudah dilakukan\n' +
                        '- Surat persetujuan sudah diupload'
                    )">
                    ✔ Tetapkan & Setujui Pemusnahan (ANRI)
                </button>
            </div>

        </div>
    </div>
</form>
@endsection
@push('scripts')
<script>
function editInline(el) {
    const td = el.closest('td');
    const inputs = td.querySelectorAll('.inline-input');

    el.classList.add('d-none');
    inputs.forEach(i => i.classList.remove('d-none'));

    inputs[0].focus();
}

// function saveInline(el) {
//     const td = el.closest('td');
//     const span = td.querySelector('.inline-text');

//     fetch("{{ route('pemusnahan.sidang.inline.update') }}", {
//         method: "POST",
//         headers: {
//             "X-CSRF-TOKEN": "{{ csrf_token() }}",
//             "Content-Type": "application/json"
//         },
//         body: JSON.stringify({
//             id: el.dataset.id,
//             model: el.dataset.model,
//             field: el.dataset.field,
//             value: el.value
//         })
//     }).then(() => {
//         span.innerText = el.value || '-';

//         td.querySelectorAll('.inline-input').forEach(i => i.classList.add('d-none'));
//         span.classList.remove('d-none');
//     });
// }
async function saveInline(el) {
    const res = await fetch("{{ route('pemusnahan.sidang.inline.update') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            id: el.dataset.id,
            model: el.dataset.model,
            field: el.dataset.field,
            value: el.value
        })
    });

    const data = await res.json();

    if (!data.success) {
        alert('Gagal menyimpan keputusan!');
        return;
    }

    const td = el.closest('td');
    const span = td.querySelector('.inline-text');

    span.innerText = el.value || '-';
    td.querySelectorAll('.inline-input').forEach(i => i.classList.add('d-none'));
    span.classList.remove('d-none');
}
</script>
@endpush
