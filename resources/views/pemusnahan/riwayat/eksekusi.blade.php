@extends('layouts.app')

@section('title', 'Konfirmasi Pemusnahan')

@section('content')
<div class="container">

    <div class="card shadow-sm">
        <div class="card-body text-center">

            <h4 class="mb-3 text-danger">⚠️ Konfirmasi Pemusnahan Arsip</h4>

            <p>
                Anda akan memusnahkan arsip tahun
                <strong>{{ $pemusnahan->tahun }}</strong>
                dengan jumlah
                <strong>{{ $pemusnahan->details()->count() }} arsip</strong>.
            </p>

            <p class="text-muted">
                Tindakan ini akan mengunci data dan memindahkan ke Riwayat Pemusnahan.
            </p>

            <form method="POST"
                  action="{{ route('pemusnahan.eksekusi.simpan', $pemusnahan->id) }}">
                @csrf

                <a href="{{ route('pemusnahan.usulan.index') }}"
                   class="btn btn-secondary">
                    Batal
                </a>

                <button class="btn btn-danger">
                    Ya, Musnahkan
                </button>
            </form>

        </div>
    </div>

</div>
@endsection
