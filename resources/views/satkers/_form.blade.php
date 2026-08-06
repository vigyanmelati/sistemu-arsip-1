@php
    $satker = $satker ?? null;
@endphp

<div class="mb-3">
    <label for="nama_satker" class="form-label">Nama Satker <span class="text-danger">*</span></label>
    <input type="text"
        name="nama_satker"
        id="nama_satker"
        class="form-control @error('nama_satker') is-invalid @enderror"
        value="{{ old('nama_satker', $satker->nama_satker ?? '') }}"
        placeholder="Contoh: KPU Kabupaten Badung"
        required>
    @error('nama_satker')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="kode_satker" class="form-label">Kode Satker</label>
    <input type="text"
        name="kode_satker"
        id="kode_satker"
        class="form-control @error('kode_satker') is-invalid @enderror"
        value="{{ old('kode_satker', $satker->kode_satker ?? '') }}"
        placeholder="Contoh: 51">
    @error('kode_satker')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">Opsional, untuk identifikasi internal.</div>
</div>

<div class="mb-3">
    <label for="alamat" class="form-label">Alamat</label>
    <textarea name="alamat"
        id="alamat"
        rows="3"
        class="form-control @error('alamat') is-invalid @enderror"
        placeholder="Alamat lengkap satker">{{ old('alamat', $satker->alamat ?? '') }}</textarea>
    @error('alamat')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-check form-switch">
    <input type="checkbox"
        name="is_active"
        id="is_active"
        class="form-check-input"
        value="1"
        {{ old('is_active', $satker->is_active ?? false) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_active">
        Jadikan satker aktif
    </label>
    @if ($satker && $satker->is_active)
        <div class="form-text text-success">Satker ini sedang aktif saat ini.</div>
    @else
        <div class="form-text">Jika dicentang, satker lain otomatis nonaktif.</div>
    @endif
</div>