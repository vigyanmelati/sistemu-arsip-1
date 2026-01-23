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
        // WAJIB
        'kode_klasifikasi_id',
        'uraian_arsip',
        'sub_bagian_id',
        'tahun_arsip',
        'tanggal_arsip',
        'jumlah_berkas',
        'satuan_arsip',
        
        // MODE PENGISIAN
        'is_isi_keterangan',
        
        // MODE 1
        'aktif_tahun',
        'inaktif_tahun',
        
        // MODE 2
        'aktif_keterangan',
        'inaktif_keterangan',
        'tanggal_referensi',
        
        // HASIL PERHITUNGAN
        'aktif_sampai',
        'inaktif_sampai',
        
        // LAINNYA
        'keterangan_jra',
        'tanggal_masuk',
        'nomor_rak',
        'nomor_box',
        'nomor_sampul',
        'status_arsip',
        'tingkat_perkembangan',
        'keterangan',
        'file_dokumen',
        'created_by'
    ];
    
    protected $casts = [
        'is_isi_keterangan' => 'boolean',
        'aktif_tahun' => 'integer',
        'inaktif_tahun' => 'integer',
        'jumlah_berkas' => 'integer',
    ];
    
    protected $attributes = [
        'is_isi_keterangan' => 0,
        'status_arsip' => 'AKTIF',
        'jumlah_berkas' => 1,
        'satuan_arsip' => 'LEMBAR',
        'aktif_tahun' => 0,
        'inaktif_tahun' => 0,
        'nomor_rak' => '',
        'nomor_box' => '',
        'nomor_sampul' => '',
        'keterangan_jra' => 'MUSNAH',
        'keterangan' => 'BAIK',
        'tingkat_perkembangan' => 'ASLI',
        'aktif_sampai' => null,
        'inaktif_sampai' => null,
    ];
    
    protected $dates = [
        'tanggal_arsip',
        'tanggal_referensi',
        'aktif_sampai', 
        'inaktif_sampai'
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
    
    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($arsip) {
            $arsip->hitungSemua();
        });
    }
    
    /**
     * Hitung semua berdasarkan mode
     */
    public function hitungSemua()
    {
        // PRIORITAS 1: Jika keterangan_jra = PERMANEN, langsung set status
        if ($this->keterangan_jra === 'PERMANEN') {
            $this->status_arsip = 'PERMANEN';
            return;
        }
        
        // MODE 1: TIDAK ISI KETERANGAN (OTOMATIS)
        if (!$this->is_isi_keterangan) {
            $this->hitungModeOtomatis();
        }
        // MODE 2: ISI KETERANGAN (DESKRIPTIF)
        else {
            $this->hitungModeDeskriptif();
        }
        
        // Default status jika kosong
        if (empty($this->status_arsip)) {
            $this->status_arsip = 'AKTIF';
        }
    }
    
    /**
     * MODE 1: Hitung otomatis (tidak isi keterangan)
     */
    protected function hitungModeOtomatis()
    {
        // 1. Hitung aktif_sampai dari tanggal_arsip
        if ($this->tanggal_arsip && $this->aktif_tahun > 0) {
            $this->aktif_sampai = Carbon::parse($this->tanggal_arsip)
                ->addYears($this->aktif_tahun);
        } else {
            // Default jika tidak ada
            $this->aktif_sampai = Carbon::parse($this->tanggal_arsip);
        }
        
        // 2. Hitung inaktif_sampai dari aktif_sampai
        if ($this->aktif_sampai && $this->inaktif_tahun > 0) {
            $this->inaktif_sampai = Carbon::parse($this->aktif_sampai)
                ->addYears($this->inaktif_tahun);
        } else {
            // Default jika tidak ada
            $this->inaktif_sampai = $this->aktif_sampai;
        }
        
        // 3. Hitung status berdasarkan tanggal sekarang
        $this->hitungStatusDariTanggal();
    }
    
    /**
     * MODE 2: Hitung deskriptif (isi keterangan)
     */
    protected function hitungModeDeskriptif()
    {
        // Jika ada tanggal_referensi, hitung aktif_sampai dan inaktif_sampai
        if ($this->tanggal_referensi) {
            // Ekstrak angka tahun dari keterangan (contoh: "1 Tahun Setelah..." → 1)
            $aktifTahun = $this->ekstrakTahunDariKeterangan($this->aktif_keterangan);
            $inaktifTahun = $this->ekstrakTahunDariKeterangan($this->inaktif_keterangan);
            
            if ($aktifTahun) {
                $this->aktif_sampai = Carbon::parse($this->tanggal_referensi)
                    ->addYears($aktifTahun);
            } else {
                $this->aktif_sampai = Carbon::parse($this->tanggal_referensi);
            }
            
            if ($this->aktif_sampai && $inaktifTahun) {
                $this->inaktif_sampai = Carbon::parse($this->aktif_sampai)
                    ->addYears($inaktifTahun);
            } else {
                $this->inaktif_sampai = $this->aktif_sampai;
            }
            
            // Hitung status berdasarkan tanggal sekarang
            $this->hitungStatusDariTanggal();
        } else {
            // Jika tanggal_referensi KOSONG, set default
            $this->status_arsip = 'AKTIF';
            $this->aktif_sampai = Carbon::now();
            $this->inaktif_sampai = Carbon::now();
        }
    }
    
    /**
     * Hitung status berdasarkan tanggal sekarang
     */
    protected function hitungStatusDariTanggal()
    {
        $now = Carbon::now();
        
        // 1. Cek jika keterangan_jra = MUSNAH dan sudah lewat inaktif_sampai + 1 tahun
        if ($this->keterangan_jra === 'MUSNAH' && $this->inaktif_sampai) {
            $tahunSetelahInaktif = Carbon::parse($this->inaktif_sampai)->addYear();
            if ($now->greaterThanOrEqualTo($tahunSetelahInaktif)) {
                $this->status_arsip = 'USUL_MUSNAH';
                return;
            }
        }
        
        // 2. Cek masa aktif/inaktif
        if ($this->aktif_sampai && $now->lessThanOrEqualTo($this->aktif_sampai)) {
            $this->status_arsip = 'AKTIF';
        } elseif ($this->inaktif_sampai && $now->lessThanOrEqualTo($this->inaktif_sampai)) {
            $this->status_arsip = 'INAKTIF';
        } elseif ($this->inaktif_sampai && $now->greaterThan($this->inaktif_sampai)) {
            // Sudah lewat masa inaktif
            $this->status_arsip = 'INAKTIF'; // atau USUL_MUSNAH jika MUSNAH (sudah dicek di atas)
        } else {
            // Default
            $this->status_arsip = 'AKTIF';
        }
    }
    
    /**
     * Ekstrak angka tahun dari keterangan
     * Contoh: "1 Tahun Setelah Barang Tidak Dikuasai" → 1
     */
    protected function ekstrakTahunDariKeterangan($keterangan)
    {
        if (empty($keterangan)) return 0;
        
        // Cari angka di awal string
        if (preg_match('/^(\d+)/', $keterangan, $matches)) {
            return (int) $matches[1];
        }
        
        return 0;
    }
    
    /**
     * Accessor untuk menampilkan tahun dari keterangan
     */
    public function getAktifTahunFromKeteranganAttribute()
    {
        if ($this->is_isi_keterangan && $this->aktif_keterangan) {
            return $this->ekstrakTahunDariKeterangan($this->aktif_keterangan);
        }
        return $this->aktif_tahun;
    }
    
    public function getInaktifTahunFromKeteranganAttribute()
    {
        if ($this->is_isi_keterangan && $this->inaktif_keterangan) {
            return $this->ekstrakTahunDariKeterangan($this->inaktif_keterangan);
        }
        return $this->inaktif_tahun;
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
}