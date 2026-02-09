{{-- resources/views/arsip-masuk/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2>Dashboard Arsip Masuk</h2>
            
            <!-- Statistik Utama -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Diajukan</h5>
                            <h2>{{ $totalDiajukan }}</h2>
                            <small>Total Arsip Diajukan</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Diterima</h5>
                            <h2>{{ $totalDiterima }}</h2>
                            <small>Total Arsip Diterima</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5 class="card-title">Ditolak</h5>
                            <h2>{{ $totalDitolak }}</h2>
                            <small>Total Arsip Ditolak</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Dipindahkan</h5>
                            <h2>{{ $totalDipindahkan }}</h2>
                            <small>Total Arsip Dipindahkan</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Arsip Baru 7 Hari -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Arsip Baru (7 Hari Terakhir)</h5>
                </div>
                <div class="card-body">
                    <h3>{{ $arsipBaru }} Arsip</h3>
                    <p class="text-muted">Arsip yang diajukan dalam 7 hari terakhir</p>
                </div>
            </div>

            <!-- Arsip Per Sub Bagian -->
            <div class="card">
                <div class="card-header">
                    <h5>Arsip Diajukan Per Sub Bagian</h5>
                </div>
                <div class="card-body">
                    @if($arsipPerSubBagian->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Sub Bagian</th>
                                    <th>Jumlah Arsip</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($arsipPerSubBagian as $data)
                                <tr>
                                    <td>{{ $data->subBagian->nama ?? 'Tidak Diketahui' }}</td>
                                    <td>{{ $data->total }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-center">Tidak ada data arsip diajukan</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection