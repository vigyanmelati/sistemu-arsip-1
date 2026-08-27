<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunBackupJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupController extends Controller
{
    protected string $disk = 'local';
    protected string $folder = 'backups';

    public function index()
    {
        $files = collect(Storage::disk($this->disk)->files($this->folder))
            ->filter(fn ($f) => str_ends_with($f, '.zip'))
            ->map(fn ($f) => [
                'name' => basename($f),
                'path' => $f,
                'size' => round(Storage::disk($this->disk)->size($f) / 1048576, 2) . ' MB',
                'date' => Storage::disk($this->disk)->lastModified($f),
            ])
            ->sortByDesc('date')
            ->values();

        return view('admin.backup.index', compact('files'));
    }

    public function store()
    {
        $statusKey = 'backup-status-' . Str::uuid();
        RunBackupJob::dispatch($statusKey);

        return response()->json(['status_key' => $statusKey]);
    }

    public function status(string $key)
    {
        return response()->json(Cache::get($key, ['percent' => 0, 'message' => 'Menunggu antrian...', 'done' => false]));
    }

    public function download(string $filename)
    {
        $path = "{$this->folder}/{$filename}";

        if (!Storage::disk($this->disk)->exists($path)) {
            abort(404);
        }

        // streamDownload agar file besar tidak dimuat penuh ke memori
        return Storage::disk($this->disk)->download($path);
    }

    public function destroy(string $filename)
    {
        Storage::disk($this->disk)->delete("{$this->folder}/{$filename}");
        return back()->with('success', 'Backup dihapus.');
    }
}