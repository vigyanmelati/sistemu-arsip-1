@extends('layouts.app')

@section('title', 'Penelitian/Penilaian Pemusnahan Arsip')

@section('content')
<style>

</style>
<div class="container-fluid">

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif


    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Penelitian/Penilaian Pemusnahan Arsip</h4>
            <small class="text-muted">Tahun {{ $pemusnahan->tahun }}</small>
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
            <li>Keputusan bersifat <b>final</b> setelah ditetapkan</li>
            <li>Minimal <b>1 arsip</b> diputuskan <b>Musnah</b></li>
            <li>Perubahan data langsung <b>tersimpan otomatis</b></li>
        </ul>
    </div>

<div class="card shadow-sm mb-3">
    <div class="card-body">

        <form method="GET">

            <div class="row">

                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Cari uraian arsip..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-2">
                    <input type="number"
                           name="tahun"
                           class="form-control"
                           placeholder="Tahun"
                           value="{{ request('tahun') }}">
                </div>

                <div class="col-md-2">
                    <select name="keputusan" class="form-select">

                        <option value="">Semua Keputusan</option>

                        <option value="musnah"
                            {{ request('keputusan') == 'musnah' ? 'selected' : '' }}>
                            Musnah
                        </option>

                        <option value="inaktif"
                            {{ request('keputusan') == 'inaktif' ? 'selected' : '' }}>
                            Inaktif
                        </option>

                        <option value="belum_dinilai"
                            {{ request('keputusan') == 'belum_dinilai' ? 'selected' : '' }}>
                            Belum Dinilai
                        </option>

                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">

                    <button class="btn btn-primary">
                        Search
                    </button>

                    <a href="{{ route('pemusnahan.sidang', $pemusnahan->id) }}"
                       class="btn btn-secondary">
                        Reset
                    </a>

                </div>

            </div>

        </form>

    </div>
</div>

    {{-- ================= BULK ACTION BAR ================= --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap align-items-center gap-2">
            <span class="fw-bold me-2">
                Terpilih: <span id="selectedCount">0</span> arsip
            </span>

            <select id="bulkKeputusan" class="form-select form-select-sm" style="width:auto;">
                <option value="">-- Pilih Keputusan --</option>
                <option value="musnah">Musnah</option>
                <option value="inaktif">Inaktif</option>
            </select>

            <button type="button"
                    class="btn btn-sm btn-primary"
                    id="applyBulkBtn"
                    disabled
                    onclick="applyBulkKeputusan()">
                Terapkan ke Terpilih
            </button>

            <button type="button"
                    class="btn btn-sm btn-outline-secondary"
                    onclick="clearSelection()">
                Batal Pilih
            </button>
        </div>
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
                        <th style="width:36px;">
                            <input type="checkbox" id="checkAll" onclick="toggleAll(this)">
                        </th>
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

                @forelse ($details as $i => $detail)
                <tr data-row-id="{{ $detail->id }}">

                    {{-- CHECKBOX --}}
                    <td class="text-center">
                        <input type="checkbox"
                               class="row-check"
                               value="{{ $detail->id }}"
                               onclick="updateSelectionState()">
                    </td>

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
    <div class="position-relative">
        <div class="form-select form-select-sm inline-text"
             onclick="editInline(this)"
             style="cursor: pointer;">
            {{ $detail->keputusan
                ? ucfirst(str_replace('_', ' ', $detail->keputusan))
                : 'Pilih Keputusan' }}
        </div>

        <select class="form-select form-select-sm d-none inline-input"
                data-model="detail"
                data-id="{{ $detail->id }}"
                data-field="keputusan"
                onchange="saveInline(this)">
            <option value="">-- Pilih --</option>
            <option value="musnah"
                {{ $detail->keputusan == 'musnah' ? 'selected' : '' }}>
                Musnah
            </option>
            <option value="inaktif"
                {{ $detail->keputusan == 'inaktif' ? 'selected' : '' }}>
                Inaktif
            </option>
        </select>
    </div>
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
                @empty
<tr>
    <td colspan="12" class="text-center text-muted py-4">
        Tidak ada arsip yang cocok dengan filter.
    </td>
</tr>
@endforelse

                </tbody>
            </table>
        </div>
    </div>

    {{-- ================= FINALISASI ================= --}}
    <form action="{{ route('pemusnahan.finalisasi', $pemusnahan->id) }}" method="POST">
    @csrf
    <div class="text-end">
        <button class="btn btn-danger"
                onclick="return confirm('Tetapkan hasil penelitian/penilaian? Data arsip tidak bisa diubah.')">
            ✔ Tetapkan Hasil Penelitian/Penilaian Arsip
        </button>
    </div>
    </form>


</div>
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

/* ================= BULK SELECTION ================= */

function toggleAll(source) {
    document.querySelectorAll('.row-check').forEach(cb => {
        cb.checked = source.checked;
    });
    updateSelectionState();
}

function clearSelection() {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = false);
    document.getElementById('checkAll').checked = false;
    updateSelectionState();
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.row-check:checked'))
        .map(cb => cb.value);
}

function updateSelectionState() {
    const selected = getSelectedIds();
    document.getElementById('selectedCount').innerText = selected.length;

    const applyBtn = document.getElementById('applyBulkBtn');
    const keputusan = document.getElementById('bulkKeputusan').value;
    applyBtn.disabled = !(selected.length > 0 && keputusan);

    const allChecks = document.querySelectorAll('.row-check');
    const checkAll = document.getElementById('checkAll');
    checkAll.checked = allChecks.length > 0 && selected.length === allChecks.length;
}

document.getElementById('bulkKeputusan').addEventListener('change', updateSelectionState);

async function applyBulkKeputusan() {
    const ids = getSelectedIds();
    const keputusan = document.getElementById('bulkKeputusan').value;

    if (ids.length === 0 || !keputusan) return;

    const label = keputusan === 'musnah' ? 'MUSNAH' : 'INAKTIF';
    if (!confirm(`Tetapkan keputusan "${label}" untuk ${ids.length} arsip terpilih?`)) {
        return;
    }

    const applyBtn = document.getElementById('applyBulkBtn');
    applyBtn.disabled = true;
    applyBtn.innerText = 'Menyimpan...';

    try {
        const res = await fetch("{{ route('pemusnahan.sidang.bulk.update', $pemusnahan->id) }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({
                ids: ids,
                keputusan: keputusan
            })
        });

        // Ambil teks mentah dulu supaya kalau bukan JSON (mis. halaman error
        // Laravel / redirect login) kita bisa lihat isinya di console, bukan
        // cuma dapat pesan generik.
        const rawText = await res.text();
        let data;
        try {
            data = JSON.parse(rawText);
        } catch (parseErr) {
            console.error('Respons bukan JSON. Status:', res.status, 'Body:', rawText);
            alert(
                res.status === 419
                    ? 'Sesi kamu sudah kedaluwarsa (419). Silakan muat ulang halaman lalu coba lagi.'
                    : `Server mengembalikan respons tak terduga (status ${res.status}). Cek console untuk detail.`
            );
            return;
        }

        if (!res.ok || !data.success) {
            console.error('Bulk update gagal:', data);
            alert(data.message || (data.errors ? JSON.stringify(data.errors) : 'Gagal menyimpan keputusan secara massal.'));
            return;
        }

        // Update UI for all selected rows without reloading the page
        ids.forEach(id => {
            const row = document.querySelector(`tr[data-row-id="${id}"]`);
            if (!row) return;
            const keputusanCell = row.querySelector('td:nth-child(10) .inline-text');
            const keputusanSelect = row.querySelector('td:nth-child(10) select.inline-input');
            if (keputusanCell) {
                keputusanCell.innerText = keputusan === 'musnah' ? 'Musnah' : 'Inaktif';
            }
            if (keputusanSelect) {
                keputusanSelect.value = keputusan;
            }
        });

        clearSelection();
    } catch (e) {
        console.error('Fetch error:', e);
        alert('Terjadi kesalahan jaringan saat menyimpan keputusan secara massal.');
    } finally {
        applyBtn.innerText = 'Terapkan ke Terpilih';
        updateSelectionState();
    }
}
</script>
@endpush
