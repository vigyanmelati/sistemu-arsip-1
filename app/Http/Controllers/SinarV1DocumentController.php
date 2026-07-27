<?php

namespace App\Http\Controllers;

use App\Models\SinarV1Document;
use App\Models\SubBagian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SinarV1DocumentController extends Controller
{
    public function importPage(Request $request)
    {
        abort_unless($this->canVerify($request), 403);

        $stageToken = $request->session()->get('sinar_v1_stage_token');
        if (! $stageToken) {
            $stageToken = (string) Str::uuid();
            $request->session()->put('sinar_v1_stage_token', $stageToken);
        }

        $stageDirectory = $this->stageDirectory($request, $stageToken);
        $stagedFiles = collect(Storage::disk('local')->allFiles($stageDirectory.'/uploads'));
        $stagedBytes = $stagedFiles->sum(fn ($file) => Storage::disk('local')->size($file));
        $stats = SinarV1Document::selectRaw('status_file, COUNT(*) as total')->groupBy('status_file')->pluck('total', 'status_file');

        return view('sinar_v1.import', compact(
            'stats', 'stagedFiles', 'stagedBytes'
        ));
    }

    public function stageFiles(Request $request)
    {
        abort_unless($this->canVerify($request), 403);

        $validated = $request->validate([
            'files' => ['required', 'array', 'max:15'],
            'files.*' => ['required', 'file', 'max:20480'],
            'paths' => ['required', 'array'],
            'paths.*' => ['required', 'string', 'max:1000'],
        ]);

        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt'];
        $stageToken = $request->session()->get('sinar_v1_stage_token') ?: (string) Str::uuid();
        $request->session()->put('sinar_v1_stage_token', $stageToken);
        $stageDirectory = $this->stageDirectory($request, $stageToken);
        $stored = 0;
        $rejected = [];
        $existingBytes = collect(Storage::disk('local')->allFiles($stageDirectory.'/uploads'))
            ->sum(fn ($path) => Storage::disk('local')->size($path));
        $incomingBytes = collect($validated['files'])->sum(fn ($file) => $file->getSize());
        abort_if($existingBytes + $incomingBytes > 5 * 1024 * 1024 * 1024, 422,
            'Ukuran staging lampiran dibatasi maksimal 5 GB per pengguna.');

        foreach ($validated['files'] as $index => $file) {
            $extension = strtolower($file->getClientOriginalExtension());
            $relativePath = $this->normalizeUploadPath($validated['paths'][$index] ?? '');

            if (! in_array($extension, $allowedExtensions, true) || ! $relativePath) {
                $rejected[] = $file->getClientOriginalName();

                continue;
            }

            $directory = $stageDirectory.'/'.str_replace('\\', '/', dirname($relativePath));
            $file->storeAs($directory, basename($relativePath), 'local');
            $stored++;
        }

        $allFiles = collect(Storage::disk('local')->allFiles($stageDirectory.'/uploads'));

        return response()->json([
            'stored' => $stored,
            'rejected' => $rejected,
            'total_files' => $allFiles->count(),
            'total_bytes' => $allFiles->sum(fn ($file) => Storage::disk('local')->size($file)),
        ]);
    }

    public function clearStaging(Request $request)
    {
        abort_unless($this->canVerify($request), 403);
        $stageToken = $request->session()->pull('sinar_v1_stage_token');
        if ($stageToken) {
            Storage::disk('local')->deleteDirectory($this->stageDirectory($request, $stageToken));
        }

        return back()->with('success', 'Folder staging lampiran SINAR V1 berhasil dikosongkan.');
    }

    public function runImport(Request $request)
    {
        abort_unless($this->canVerify($request), 403);

        $validated = $request->validate([
            'mode' => ['required', Rule::in(['dry-run', 'commit'])],
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'integer', 'between:1,65535'],
            'db_database' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9_-]+$/'],
            'db_username' => ['required', 'string', 'max:128'],
            'db_password' => ['nullable', 'string', 'max:500'],
            'confirmation' => [Rule::requiredIf($request->input('mode') === 'commit'), 'nullable', 'accepted'],
            'skip_files' => ['nullable', 'boolean'],
        ]);

        $this->configureRuntimeSource($validated);

        $stageToken = $request->session()->get('sinar_v1_stage_token');
        $stageRoot = $stageToken ? storage_path('app/private/'.$this->stageDirectory($request, $stageToken)) : null;
        config([
            'sinar_v1.connection' => 'sinar_v1_runtime',
            'sinar_v1.files_root' => $stageRoot,
            'sinar_v1.copy_files' => ! $request->boolean('skip_files'),
        ]);

        $arguments = [];
        if ($validated['mode'] === 'commit') {
            $arguments['--commit'] = true;
        }
        if ($request->boolean('skip_files')) {
            $arguments['--skip-files'] = true;
        }

        $exitCode = Artisan::call('sinar-v1:migrate', $arguments);
        $output = trim(Artisan::output());

        if ($request->expectsJson()) {
            return response()->json(['success' => $exitCode === 0, 'output' => $output], $exitCode === 0 ? 200 : 422);
        }

        return back()->with($exitCode === 0 ? 'success' : 'error',
            ($validated['mode'] === 'commit' ? 'Proses import selesai.' : 'Dry-run selesai.').' Lihat keluaran proses di bawah.'
        )->with('import_output', $output);
    }

    private function configureRuntimeSource(array $data): void
    {
        config(['database.connections.sinar_v1_runtime' => [
            'driver' => 'mysql', 'host' => $data['db_host'], 'port' => $data['db_port'],
            'database' => $data['db_database'], 'username' => $data['db_username'],
            'password' => $data['db_password'] ?? '', 'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'strict' => false,
        ]]);
        DB::purge('sinar_v1_runtime');
    }

    private function stageDirectory(Request $request, string $token): string
    {
        return 'sinar_v1_staging/'.$request->user()->id.'/'.$token;
    }

    private function normalizeUploadPath(string $path): ?string
    {
        $path = str_replace('\\', '/', trim($path));
        $parts = array_values(array_filter(explode('/', $path), fn ($part) => $part !== ''));
        $uploadsIndex = array_search('uploads', array_map('strtolower', $parts), true);
        if ($uploadsIndex === false) {
            return null;
        }

        $parts = array_slice($parts, $uploadsIndex);
        foreach ($parts as $part) {
            if ($part === '.' || $part === '..' || preg_match('/[\x00-\x1F<>:"|?*]/', $part)) {
                return null;
            }
        }

        return implode('/', $parts);
    }

    public function index(Request $request)
    {
        $query = SinarV1Document::with('subBagian')->visibleTo($request->user());

        $query->when($request->filled('search'), function ($query) use ($request) {
            $search = $request->string('search')->trim();
            $query->where(function ($filter) use ($search) {
                $filter->where('nomor_dokumen', 'like', "%{$search}%")
                    ->orWhere('perihal', 'like', "%{$search}%")
                    ->orWhere('instansi_satker', 'like', "%{$search}%");
            });
        })->when($request->filled('category'), fn ($q) => $q->where('legacy_category_id', $request->integer('category')))
            ->when($request->filled('year'), fn ($q) => $q->whereYear('tanggal_dokumen', $request->integer('year')))
            ->when($request->filled('hardcopy'), fn ($q) => $q->where('status_hardcopy', $request->input('hardcopy')))
            ->when($request->filled('integration'), fn ($q) => $q->where('status_integrasi', $request->input('integration')));

        $documents = $query->latest('tanggal_dokumen')->latest('id')->paginate(20)->withQueryString();
        $years = SinarV1Document::visibleTo($request->user())->whereNotNull('tanggal_dokumen')
            ->selectRaw('YEAR(tanggal_dokumen) as year')->distinct()->orderByDesc('year')->pluck('year');

        return view('sinar_v1.index', compact('documents', 'years'));
    }

    public function show(Request $request, SinarV1Document $document)
    {
        $this->authorizeDocument($request, $document);

        return view('sinar_v1.show', [
            'document' => $document->load(['subBagian', 'verifier', 'arsip']),
            'subBagians' => SubBagian::orderBy('nama_sub_bagian')->get(),
        ]);
    }

    public function updateVerification(Request $request, SinarV1Document $document)
    {
        $this->authorizeDocument($request, $document);
        abort_unless($this->canVerify($request), 403);

        $validated = $request->validate([
            'sub_bagian_id' => ['nullable', 'exists:sub_bagians,id'],
            'status_hardcopy' => ['required', Rule::in(array_keys(SinarV1Document::HARDCOPY_STATUSES))],
            'status_integrasi' => ['required', Rule::in(array_keys(SinarV1Document::INTEGRATION_STATUSES))],
            'lokasi_hardcopy' => ['nullable', 'string', 'max:255'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validated['status_integrasi'] === 'SIAP_DIDAFTARKAN' &&
            ! in_array($validated['status_hardcopy'], ['DITEMUKAN', 'HANYA_DIGITAL'], true)) {
            return back()->withErrors([
                'status_integrasi' => 'Dokumen hanya dapat disiapkan menjadi Arsip V2 setelah hardcopy ditemukan atau dinyatakan hanya digital.',
            ])->withInput();
        }

        $document->update($validated + [
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Hasil verifikasi dokumen SINAR V1 berhasil disimpan.');
    }

    public function download(Request $request, SinarV1Document $document)
    {
        $this->authorizeDocument($request, $document);
        abort_unless($document->file_path && Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->file_name_original);
    }

    public function prepareArchive(Request $request, SinarV1Document $document)
    {
        $this->authorizeDocument($request, $document);
        abort_unless($this->canVerify($request), 403);
        abort_if($document->arsip_id, 422, 'Dokumen sudah terhubung dengan Arsip V2.');
        abort_unless(in_array($document->status_hardcopy, ['DITEMUKAN', 'HANYA_DIGITAL'], true), 422,
            'Verifikasi hardcopy terlebih dahulu sebelum mendaftarkan dokumen sebagai arsip.');

        $document->update(['status_integrasi' => 'SIAP_DIDAFTARKAN']);

        return redirect()->route('arsip.create', ['sinar_v1_document' => $document->id]);
    }

    private function authorizeDocument(Request $request, SinarV1Document $document): void
    {
        $visible = SinarV1Document::visibleTo($request->user())->whereKey($document->id)->exists();
        abort_unless($visible, 403);
    }

    private function canVerify(Request $request): bool
    {
        return in_array(strtolower((string) $request->user()->role), ['admin', 'super_admin', 'tu'], true);
    }
}
