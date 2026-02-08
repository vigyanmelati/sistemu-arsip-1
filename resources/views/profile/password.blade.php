@extends('layouts.app')

@section('title', 'Ubah Password')

@section('content')
<div class="container">
    <h3 class="mb-4">Ubah Password</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('profile.password.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="current_password" class="form-label">Password Lama</label>
            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                   id="current_password" name="current_password" required>
            @error('current_password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password Baru</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror"
                   id="password" name="password" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" class="form-control" id="password_confirmation"
                   name="password_confirmation" required>
        </div>

        <button type="submit" class="btn btn-primary">Ubah Password</button>
    </form>
</div>
@endsection
