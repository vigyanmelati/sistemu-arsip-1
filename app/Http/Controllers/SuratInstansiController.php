<?php

namespace App\Http\Controllers;

use App\Models\SuratInstansi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\SuratMasuk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuratInstansiController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $items = SuratInstansi::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($filter) use ($search) {
                    foreach (['nama_instansi', 'alamat', 'telepon', 'fax', 'email', 'website'] as $index => $field) {
                        $method = $index === 0 ? 'where' : 'orWhere';
                        $filter->{$method}($field, 'like', "%{$search}%");
                    }
                });
            })
            ->orderBy('nama_instansi')
            ->paginate(20)
            ->withQueryString();

        return view('surat_masuk.master.instansi', compact('items', 'search'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        SuratInstansi::create($data + ['aktif' => true, 'created_by' => auth()->id()]);
        return back()->with('success', 'Instansi/Satker berhasil ditambahkan.');
    }

    public function quickStore(Request $request)
    {
        $data = $this->validateData($request);
        $instansi = SuratInstansi::create($data + ['aktif' => true, 'created_by' => auth()->id()]);

        return response()->json([
            'message' => 'Instansi/Satker berhasil ditambahkan.',
            'instansi' => ['id' => $instansi->id, 'nama_instansi' => $instansi->nama_instansi],
        ], 201);
    }

    public function duplicates()
    {
        $groups = SuratInstansi::withCount('suratMasuks')->orderBy('nama_instansi')->get()
            ->groupBy(fn (SuratInstansi $item) => $this->normalizeName($item->nama_instansi))
            ->filter(fn ($items, $key) => $key !== '' && $items->count() > 1)
            ->values();

        return view('surat_masuk.master.instansi_duplicates', compact('groups'));
    }

    public function mergeDuplicates(Request $request)
    {
        $data = $request->validate([
            'canonical_id' => 'required|exists:surat_instansis,id',
            'duplicate_ids' => 'required|array|min:1',
            'duplicate_ids.*' => 'distinct|exists:surat_instansis,id|different:canonical_id',
        ]);

        $canonical = SuratInstansi::findOrFail($data['canonical_id']);
        $duplicates = SuratInstansi::whereIn('id', $data['duplicate_ids'])->get();
        abort_unless(
            $duplicates->isNotEmpty() && $duplicates->every(fn ($item) => $this->normalizeName($item->nama_instansi) === $this->normalizeName($canonical->nama_instansi)),
            422,
            'Data yang dipilih bukan kelompok nama instansi yang sama.'
        );

        $moved = 0;
        DB::transaction(function () use ($canonical, $duplicates, &$moved) {
            foreach (['alamat', 'telepon', 'fax', 'email', 'website'] as $field) {
                if (! $canonical->{$field}) {
                    $canonical->{$field} = $duplicates->pluck($field)->first(fn ($value) => filled($value));
                }
            }
            $canonical->aktif = true;
            $canonical->save();

            $duplicateIds = $duplicates->pluck('id');
            $moved = SuratMasuk::whereIn('instansi_id', $duplicateIds)->update([
                'instansi_id' => $canonical->id,
                'instansi_satker' => $canonical->nama_instansi,
            ]);
            SuratInstansi::whereIn('id', $duplicateIds)->delete();
        });

        return redirect()->route('surat-instansi.duplicates')->with('success',
            "Duplikat berhasil digabungkan ke {$canonical->nama_instansi}. {$moved} Surat Masuk diperbarui."
        );
    }

    public function update(Request $request, SuratInstansi $instansi)
    {
        $data = $this->validateData($request, $instansi);
        $instansi->update($data + ['aktif' => $request->boolean('aktif')]);
        return back()->with('success', 'Instansi/Satker berhasil diperbarui.');
    }

    private function validateData(Request $request, ?SuratInstansi $instansi = null): array
    {
        return $request->validate([
            'nama_instansi' => ['required', 'string', 'max:255', Rule::unique('surat_instansis', 'nama_instansi')->ignore($instansi)],
            'alamat' => 'nullable|string|max:255',
            'telepon' => ['nullable', 'regex:/^[0-9]+$/', 'max:30'],
            'fax' => ['nullable', 'regex:/^[0-9]+$/', 'max:30'],
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'aktif' => 'nullable|boolean',
        ], [
            'telepon.regex' => 'Nomor telepon hanya boleh berisi angka.',
            'fax.regex' => 'Nomor fax hanya boleh berisi angka.',
            'website.url' => 'Alamat web harus berupa URL lengkap, misalnya https://kpu.go.id.',
        ]);
    }

    private function normalizeName(string $name): string
    {
        return preg_replace('/[^a-z0-9]+/', '', Str::lower(Str::ascii(trim($name)))) ?? '';
    }
}
