@extends('layouts.app')

@section('content')
{{ auth()->user()->name }}
<div class="p-6">
    <h2 class="text-2xl font-bold mb-4">Selamat Datang di Sistem Arsip KPU Bali</h2>
    <p>Gunakan menu untuk mengelola arsip dokumen.</p>
</div>
@endsection
