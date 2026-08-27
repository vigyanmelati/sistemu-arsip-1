@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Backup Sistem</h1>

    <button id="btn-backup" class="btn btn-primary">Buat Backup Sekarang</button>

    <div id="progress-wrapper" class="mt-3" style="display:none;">
        <div class="progress">
            <div id="progress-bar" class="progress-bar" style="width:0%">0%</div>
        </div>
        <p id="progress-message" class="text-muted mt-1"></p>
    </div>

    <table class="table mt-4">
        <thead>
            <tr><th>Nama File</th><th>Ukuran</th><th>Tanggal</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse ($files as $f)
            <tr>
                <td>{{ $f['name'] }}</td>
                <td>{{ $f['size'] }}</td>
                <td>{{ \Carbon\Carbon::createFromTimestamp($f['date'])->format('d M Y H:i') }}</td>
                <td>
                    <a href="{{ route('admin.backup.download', $f['name']) }}" class="btn btn-sm btn-success">Download</a>
                    <form action="{{ route('admin.backup.destroy', $f['name']) }}" method="POST" style="display:inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus backup ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="4">Belum ada backup.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
document.getElementById('btn-backup').addEventListener('click', function () {
    this.disabled = true;
    document.getElementById('progress-wrapper').style.display = 'block';

    fetch("{{ route('admin.backup.store') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => pollStatus(data.status_key));
});

function pollStatus(key) {
    const interval = setInterval(() => {
        fetch(`/admin/backup/status/${key}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('progress-bar').style.width = data.percent + '%';
                document.getElementById('progress-bar').innerText = data.percent + '%';
                document.getElementById('progress-message').innerText = data.message;

                if (data.done) {
                    clearInterval(interval);
                    document.getElementById('btn-backup').disabled = false;
                    if (!data.error) location.reload();
                }
            });
    }, 2000);
}
</script>
@endsection