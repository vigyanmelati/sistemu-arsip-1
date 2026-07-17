<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Arsip extends Model
{
    protected $table = 'arsips';
    protected $primaryKey = 'id';
    public $timestamps = true;
    public $incrementing = true;
    
    protected $fillable = [
        // WAJIB - Data Dasar
        'kode_klasifikasi_id',
        'uraian_arsip',
        'sub_bagian_id',
        'tahun_arsip',
        'tanggal_arsip',
        'jumlah_berkas',
        'satuan_arsip',
        
        // MODE PENGISIAN - KINI DUA MODE
        'is_isi_keterangan',        // 0 = Otomatis (angka), 1 = Deskriptif (string)
        
        // Input retensi (bisa angka ATAU string)
        'aktif_tahun',              // BISA: 2 (integer) ATAU "2 TAHUN SETELAH..." (string)
        'inaktif_tahun',            // BISA: 5 (integer) ATAU "5 TAHUN" (string)
        'tanggal_referensi',        // Untuk mode "SETELAH"
        
        // HASIL PERHITUNGAN
        'aktif_sampai',
        'inaktif_sampai',
        'status_arsip',
        
        // LAINNYA
        'keterangan_jra',           // "MUSNAH" atau "PERMANEN"
        'tanggal_masuk',
         'rak_id',
        'box_id',
       
        'nomor_sampul',
        'lokasi_arsip',
        'tingkat_perkembangan',
        'keterangan',
        'media_arsip',
        'file_dokumen',
        'link_foto',
        'created_by',
        'file_berita_acara',
        'status_pindah',
        'catatan_verifikasi',
        'diverifikasi_oleh',
        'tanggal_diverifikasi',

        'catatan_perbaikan',
        'duplicate_reason',
        'is_duplicate',
        'klasifikasi_keamanan'
    ];
     protected $guarded = ['skipHistory'];

    public bool $skipHistory = false;
    protected $attributes = [
        'is_isi_keterangan' => 0,
        'status_arsip' => 'AKTIF',
        'jumlah_berkas' => 1,
        'satuan_arsip' => 'LEMBAR',
        'aktif_tahun' => '',    
        'inaktif_tahun' => '',  
        'nomor_sampul' => '',
        'keterangan_jra' => 'BELUM DITENTUKAN',  
        'keterangan' => 'BAIK',
         'media_arsip' => 'TEKSTUAL',
        'tingkat_perkembangan' => 'ASLI',
        'aktif_sampai' => null,
        'inaktif_sampai' => null,
    ];
    
    protected $casts = [
        'tanggal_arsip' => 'date',
        'tanggal_referensi' => 'date',
        'aktif_sampai' => 'date',
        'inaktif_sampai' => 'date',
        'tanggal_masuk' => 'date',
        'jumlah_berkas' => 'integer',
        'is_isi_keterangan' => 'boolean',
    ];
    
    /**
     * RELASI KE TABEL LAIN
     */
    
    // Relasi ke KodeKlasifikasi
    public function kodeKlasifikasi(): BelongsTo
    {
        return $this->belongsTo(KodeKlasifikasi::class, 'kode_klasifikasi_id');
    }
    
    // Relasi ke SubBagian
    public function subBagian(): BelongsTo
    {
        return $this->belongsTo(SubBagian::class, 'sub_bagian_id');
    }
    
    // Relasi ke User (created_by)
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

     // satu arsip bisa ikut banyak proses pemusnahan (lintas tahun)
    public function pemusnahanDetails()
    {
        return $this->hasMany(PemusnahanDetail::class, 'arsip_id');
    }

    // helper: cek apakah arsip disetujui dimusnahkan di pemusnahan tertentu
    public function disetujuiDiPemusnahan($pemusnahanId)
    {
        return $this->pemusnahanDetails()
            ->where('pemusnahan_id', $pemusnahanId)
            ->where('keputusan', 'disetujui')
            ->exists();
    }
    /**
     * Boot method - NONAKTIFKAN SEMENTARA karena konflik
     * Biarkan Controller yang menghitung
     */
    // protected static function boot()
    // {
    //     parent::boot();
        
    //     static::saving(function ($arsip) {
    //         // JANGAN panggil hitungSemua() di sini
    //         // Biarkan Controller yang bertanggung jawab
    //     });
    // }
    
    /**
     * Ekstrak angka dari teks retensi
     * Contoh: "2 TAHUN" → 2, "2 TAHUN SETELAH KEGIATAN" → 2
     */
    public function extractNumberFromText($text)
    {
        if (is_numeric($text)) {
            return (int) $text;
        }
        
        if (preg_match('/\d+/', (string) $text, $matches)) {
            return (int) $matches[0];
        }
        
        return 0;
    }
    
    /**
     * Cek apakah teks mengandung kata "SETELAH"
     */
    public function containsSetelah($text)
    {
        return stripos((string) $text, 'setelah') !== false;
    }
    
    /**
     * Hitung retensi berdasarkan logika baru
     * Ini dipanggil dari Controller
     */
    public function hitungRetensiBaru()
    {
        // 1. Jika PERMANEN, langsung set status
        if ($this->keterangan_jra === 'PERMANEN') {
            $this->status_arsip = 'PERMANEN';
            return;
        }
        
        // 2. Ekstrak angka dari input
        $aktifAngka = $this->extractNumberFromText($this->aktif_tahun);
        $inaktifAngka = $this->extractNumberFromText($this->inaktif_tahun);
        
        // 3. Tentukan tanggal dasar
        $tanggalDasar = $this->tanggal_arsip;
        
        // Jika mengandung "SETELAH" dan ada tanggal_referensi
        if ($this->containsSetelah($this->aktif_tahun) || 
            $this->containsSetelah($this->inaktif_tahun)) {
            
            if ($this->tanggal_referensi) {
                $tanggalDasar = $this->tanggal_referensi;
            }
        }
        
        // 4. Hitung tanggal aktif sampai
        if ($aktifAngka > 0 && $tanggalDasar) {
            $this->aktif_sampai = Carbon::parse($tanggalDasar)->addYears($aktifAngka);
        } else {
            $this->aktif_sampai = Carbon::parse($tanggalDasar);
        }
        
        // 5. Hitung tanggal inaktif sampai
        if ($inaktifAngka > 0 && $this->aktif_sampai) {
            $this->inaktif_sampai = Carbon::parse($this->aktif_sampai)->addYears($inaktifAngka);
        } else {
            $this->inaktif_sampai = $this->aktif_sampai;
        }
        
        // 6. Tentukan status berdasarkan tanggal sekarang
        $this->hitungStatusDariTanggal();
    }
    
    /**
     * Hitung status berdasarkan tanggal sekarang
     */
    protected function hitungStatusDariTanggal()
    {
        $now = Carbon::now();
        
        if (!$this->aktif_sampai || !$this->inaktif_sampai) {
            $this->status_arsip = 'AKTIF';
            return;
        }
        
        // Untuk keterangan_jra = MUSNAH
        if ($this->keterangan_jra === 'MUSNAH') {
            $tahunSetelahInaktif = Carbon::parse($this->inaktif_sampai)->addYear();
            
            if ($now->greaterThanOrEqualTo($tahunSetelahInaktif)) {
                $this->status_arsip = 'MUSNAH';
            } elseif ($now->greaterThan($this->inaktif_sampai)) {
                $this->status_arsip = 'INAKTIF';
            } elseif ($now->greaterThan($this->aktif_sampai)) {
                $this->status_arsip = 'INAKTIF';
            } else {
                $this->status_arsip = 'AKTIF';
            }
        } 
        // Untuk selain MUSNAH
        else {
            if ($now->lessThanOrEqualTo($this->aktif_sampai)) {
                $this->status_arsip = 'AKTIF';
            } elseif ($now->lessThanOrEqualTo($this->inaktif_sampai)) {
                $this->status_arsip = 'INAKTIF';
            } else {
                $this->status_arsip = 'INAKTIF';
            }
        }
    }
    
    /**
     * Accessor untuk format tanggal
     */
    public function getTanggalArsipFormattedAttribute()
    {
        return $this->tanggal_arsip ? Carbon::parse($this->tanggal_arsip)->format('d-m-Y') : null;
    }
    
    public function getAktifSampaiFormattedAttribute()
    {
        return $this->aktif_sampai ? Carbon::parse($this->aktif_sampai)->format('d-m-Y') : null;
    }
    
    public function getInaktifSampaiFormattedAttribute()
    {
        return $this->inaktif_sampai ? Carbon::parse($this->inaktif_sampai)->format('d-m-Y') : null;
    }
    
    /**
     * Mutator untuk aktif_tahun - terima string atau integer
     */
    public function setAktifTahunAttribute($value)
    {
        // Terima string atau integer
        $this->attributes['aktif_tahun'] = $value;
        
        // Auto-set is_isi_keterangan jika mengandung kata
        if (is_string($value) && $this->containsSetelah($value)) {
            $this->attributes['is_isi_keterangan'] = 1;
        }
    }
    
    public function setInaktifTahunAttribute($value)
    {
        $this->attributes['inaktif_tahun'] = $value;
    }
    
    /**
     * Accessor untuk mendapatkan angka saja
     */
    public function getAktifTahunAngkaAttribute()
    {
        return $this->extractNumberFromText($this->aktif_tahun);
    }
    
    public function getInaktifTahunAngkaAttribute()
    {
        return $this->extractNumberFromText($this->inaktif_tahun);
    }
    
    /**
     * Scope untuk filtering
     */
    public function scopeFilterByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status_arsip', $status);
        }
        return $query;
    }
    
    public function scopeFilterByTahun($query, $tahun)
    {
        if ($tahun) {
            return $query->where('tahun_arsip', $tahun);
        }
        return $query;
    }
    
    public function scopeFilterBySubBagian($query, $subBagianId)
    {
        if ($subBagianId) {
            return $query->where('sub_bagian_id', $subBagianId);
        }
        return $query;
    }
    
    public function scopeFilterByKodeKlasifikasi($query, $kodeKlasifikasiId)
    {
        if ($kodeKlasifikasiId) {
            return $query->where('kode_klasifikasi_id', $kodeKlasifikasiId);
        }
        return $query;
    }

    /**
     * Accessor untuk tanggal_arsip format Y-m-d (untuk input type="date")
     */
    public function getTanggalArsipForInputAttribute()
    {
        return $this->tanggal_arsip ? $this->tanggal_arsip->format('Y-m-d') : null;
    }

    /**
     * Accessor untuk tanggal_referensi format Y-m-d
     */
    public function getTanggalReferensiForInputAttribute()
    {
        return $this->tanggal_referensi ? $this->tanggal_referensi->format('Y-m-d') : null;
    }

    /**
     * Accessor untuk tanggal_masuk format Y-m-d
     */
    public function getTanggalMasukForInputAttribute()
    {
        return $this->tanggal_masuk ? $this->tanggal_masuk->format('Y-m-d') : null;
    }

    // Arsip.php
    public function bapDetails()
    {
        return $this->hasMany(BeritaAcaraDetail::class, 'arsip_id');
    }

    // app/Models/Arsip.php
    public function historyPindah()
    {
        return $this->hasMany(HistoryPindah::class)->orderBy('tanggal_pindah', 'desc');
    }

    public function lokasiTerakhir()
    {
        return $this->historyPindah()->latest()->first();
    }

    public function beritaAcaraDetails()
    {
        return $this->hasMany(BeritaAcaraDetail::class, 'arsip_id');
    }

    public function beritaAcaraPindah()
    {
        return $this->belongsToMany(BeritaAcaraPindah::class, 'berita_acara_detail', 'arsip_id', 'bap_id')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    // Ambil BAP terakhir (karena satu arsip bisa beberapa kali diajukan? biasanya sekali)
    public function getLatestBeritaAcaraAttribute()
    {
        return $this->beritaAcaraPindah()->latest()->first();
    }

    public function getViewFileUrlAttribute()
    {
        if (!empty($this->link_foto)) {
            return $this->link_foto;
        }

        if (!empty($this->file_dokumen)) {
            return asset('storage/arsip/' . $this->file_dokumen);
        }

        return null;
    }

    public function rak()
{
    return $this->belongsTo(MasterRak::class, 'rak_id');
}

public function box()
{
    return $this->belongsTo(MasterBox::class, 'box_id');
}
  const LOKASI_ARSIP = [
        'RECORD_CENTER_PERMANEN' => 'Record Center Permanen',
        'RECORD_CENTER_INAKTIF' => 'Record Center Inaktif',
        'RUANG_SUBBAGIAN_UMUM_LOGISTIK' => 'Subbagian Umum & Logistik',
        'RUANG_SUBBAGIAN_PARTISIPASI_MASYARAKAT_SDM' => 'Subbagian Parmas & SDM',
        'RUANG_SUBBAGIAN_KEUANGAN' => 'Subbagian Keuangan',
        'RUANG_SUBBAGIAN_PERENCANAAN_DATA_INFORMASI' => 'Subbagian Perencanaan, Data & Informasi',
        'RUANG_SUBBAGIAN_TEKNIS' => 'Subbagian Teknis',
        'RUANG_SUBBAGIAN_HUKUM' => 'Subbagian Hukum',
    ];

    // Ambil semua key lokasi arsip
    public static function getLokasiArsipKeys()
    {
        return array_keys(self::LOKASI_ARSIP);
    }

    // Ambil semua label lokasi arsip
    public static function getLokasiArsipLabels()
    {
        return self::LOKASI_ARSIP;
    }

    // Ambil label dari suatu key
    public static function getLokasiArsipLabel($key)
    {
        return self::LOKASI_ARSIP[$key] ?? $key;
    }


public function hasActiveBap()
{
    return $this->beritaAcaraDetails()
        ->whereIn('status', ['DRAFT', 'DIAJUKAN', 'DITERIMA'])
        ->exists();
}

public function scopeWithoutActiveBap($query)
{
    return $query->whereDoesntHave('beritaAcaraDetails', function($q) {
        $q->whereIn('status', ['DRAFT', 'DIAJUKAN', 'DITERIMA']);
    });
}

/**
 * Scope untuk arsip yang sudah memiliki BAP
 */
public function scopeWithActiveBap($query)
{
    return $query->whereHas('beritaAcaraDetails', function($q) {
        $q->whereIn('status', ['DRAFT', 'DIAJUKAN', 'DITERIMA']);
    });
}


    const STATUS_BELUM = 'BELUM';
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_DIAJUKAN = 'DIAJUKAN';
    const STATUS_DIPERBAIKI = 'DIPERBAIKI';
    const STATUS_DITOLAK = 'DITOLAK';
    const STATUS_DIPINDAHKAN = 'DIPINDAHKAN';
    const STATUS_DITERIMA = 'DITERIMA';
    const STATUS_SELESAI = 'SELESAI';
    const STATUS_NON_ARSIP = 'NON_ARSIP';
     const STATUS_LANGSUNG = 'LANGSUNG';

    public static function getStatusPindahOptions()
    {
        return [
            self::STATUS_BELUM => 'Belum',
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_DIAJUKAN => 'Diajukan',
            self::STATUS_DIPERBAIKI => 'Diperbaiki',
            self::STATUS_DITOLAK => 'Ditolak',
            self::STATUS_DIPINDAHKAN => 'Dipindahkan',
            self::STATUS_DITERIMA => 'Diterima',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_NON_ARSIP => 'Non Arsip',
            self::STATUS_LANGSUNG => 'Langsung',
        ];
    }

     /**
     * Recalculate retensi berdasarkan data saat ini
     */
 public function recalculateRetensi()
    {
        // Jika aktif_tahun atau inaktif_tahun kosong
        if (empty($this->aktif_tahun) || empty($this->inaktif_tahun)) {
            $this->aktif_sampai = null;
            $this->inaktif_sampai = null;
            $this->status_arsip = 'AKTIF';
            return;
        }

        // Ekstrak angka dari string (contoh: "2 TAHUN" -> 2)
        $aktifTahun = $this->extractNumber($this->aktif_tahun);
        $inaktifTahun = $this->extractNumber($this->inaktif_tahun);

        // Jika tidak ada angka, set ke null
        if (!$aktifTahun || !$inaktifTahun) {
            $this->aktif_sampai = null;
            $this->inaktif_sampai = null;
            $this->status_arsip = 'AKTIF';
            return;
        }

        // Tentukan tahun dasar
        // Jika ada tanggal_arsip, gunakan tahun dari tanggal_arsip
        // Jika tidak, gunakan tahun sekarang
        if ($this->tanggal_arsip) {
            $tahunDasar = Carbon::parse($this->tanggal_arsip)->year;
        } else {
            $tahunDasar = Carbon::now()->year;
        }

        // Cek apakah mengandung kata "SETELAH"
        $aktifSetelah = stripos($this->aktif_tahun, 'SETELAH') !== false;
        $inaktifSetelah = stripos($this->inaktif_tahun, 'SETELAH') !== false;

        // Jika mengandung SETELAH dan ada tanggal_referensi, gunakan tahun dari tanggal_referensi
        if (($aktifSetelah || $inaktifSetelah) && $this->tanggal_referensi) {
            $tahunDasar = Carbon::parse($this->tanggal_referensi)->year;
        }

        // HITUNG AKTIF SAMPAI (tahun dasar + aktif_tahun, tanggal 31 Desember)
        $tahunAktifSampai = $tahunDasar + $aktifTahun;
        $this->aktif_sampai = Carbon::create($tahunAktifSampai, 12, 31)->format('Y-m-d');

        // HITUNG INAKTIF SAMPAI (tahun aktif + inaktif_tahun, tanggal 31 Desember)
        $tahunInaktifSampai = $tahunAktifSampai + $inaktifTahun;
        $this->inaktif_sampai = Carbon::create($tahunInaktifSampai, 12, 31)->format('Y-m-d');

        // TENTUKAN STATUS
        $tahunSekarang = Carbon::now()->year;
        $keterangan = strtoupper($this->keterangan_jra ?? '');

        if ($keterangan === 'PERMANEN') {
            $this->status_arsip = 'PERMANEN';
        } elseif ($keterangan === 'MUSNAH') {
            // Untuk MUSNAH: AKTIF -> INAKTIF -> HABIS RETENSI
            if ($tahunSekarang <= $tahunAktifSampai) {
                $this->status_arsip = 'AKTIF';
            } elseif ($tahunSekarang <= $tahunInaktifSampai) {
                $this->status_arsip = 'INAKTIF';
            } else {
                $this->status_arsip = 'HABIS_RETENSI';
            }
        } else {
            // Untuk selain MUSNAH: AKTIF -> INAKTIF
            if ($tahunSekarang <= $tahunAktifSampai) {
                $this->status_arsip = 'AKTIF';
            } else {
                $this->status_arsip = 'INAKTIF';
            }
        }
    }

    /**
     * Ekstrak angka dari teks (contoh: "2 TAHUN" -> 2, "5 TAHUN SETELAH KEGIATAN" -> 5)
     */
    private function extractNumber($text)
    {
        if (empty($text)) {
            return null;
        }
        
        if (preg_match('/\d+/', $text, $matches)) {
            return (int) $matches[0];
        }
        
        return null;
    }

    /**
     * Update field dan recalculate retensi
     */
    public function updateFieldAndRecalculate($field, $value)
    {
        $this->$field = $value;
        
        // Cek apakah field yang diupdate mempengaruhi retensi
        $retensiFields = ['aktif_tahun', 'inaktif_tahun', 'keterangan_jra', 'tanggal_arsip', 'tanggal_referensi'];
        
        if (in_array($field, $retensiFields)) {
            $this->recalculateRetensi();
        }
        
        $this->save();
        
        return $this;
    }

    /**
 * Accessor untuk mendapatkan status arsip
 * Untuk kompatibilitas dengan view yang menggunakan $arsip->status
 */
public function getStatusAttribute()
{
    return $this->status_arsip;
}

/**
 * Mutator untuk status - simpan ke status_arsip
 */
public function setStatusAttribute($value)
{
    $this->attributes['status_arsip'] = $value;
}

public function verifikator(): BelongsTo
{
    return $this->belongsTo(User::class, 'diverifikasi_oleh');
}
}