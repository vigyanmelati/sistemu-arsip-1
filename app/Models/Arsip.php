<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class Arsip extends Model
{
    protected $table = 'arsips';
    protected $primaryKey = 'id';
    public $timestamps = true;

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
        'is_isi_keterangan', // true/false
        
        // MODE 1: Tidak isi keterangan (otomatis)
        'aktif_tahun',       // angka: 1
        'inaktif_tahun',     // angka: 5
        
        // MODE 2: Isi keterangan (deskriptif)
        'aktif_keterangan',  // deskripsi: "1 Tahun Setelah Barang Tidak Dikuasai"
        'inaktif_keterangan', // deskripsi: "5 Tahun Setelah UU..."
        'tanggal_referensi',  // tanggal acuan
        
        // HASIL PERHITUNGAN
        'aktif_sampai',
        'inaktif_sampai',
        
        // LAINNYA
        'keterangan_jra',    // MUSNAH/PERMANEN
        'status_arsip',
        'nomor_rak',
        'nomor_box',
        'no_sampul',
        'tingkat_perkembangan',
        'keterangan', // kondisi fisik
        'file_dokumen',
        'created_by'
    ];
    
    protected $casts = [
        'is_isi_keterangan' => 'boolean',
    ];
    
    protected $dates = [
        'tanggal_arsip',
        'tanggal_referensi',
        'aktif_sampai', 
        'inaktif_sampai'
    ];
    
    protected $attributes = [
        'is_isi_keterangan' => false,
        'status_arsip' => 'AKTIF',
        'jumlah_berkas' => 1,
        'satuan_arsip' => 'LEMBAR',
    ];
    
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
        if ($this->tanggal_arsip && $this->aktif_tahun) {
            $this->aktif_sampai = Carbon::parse($this->tanggal_arsip)
                ->addYears($this->aktif_tahun);
        }
        
        // 2. Hitung inaktif_sampai dari aktif_sampai
        if ($this->aktif_sampai && $this->inaktif_tahun) {
            $this->inaktif_sampai = Carbon::parse($this->aktif_sampai)
                ->addYears($this->inaktif_tahun);
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
            }
            
            if ($this->aktif_sampai && $inaktifTahun) {
                $this->inaktif_sampai = Carbon::parse($this->aktif_sampai)
                    ->addYears($inaktifTahun);
            }
            
            // Hitung status berdasarkan tanggal sekarang
            $this->hitungStatusDariTanggal();
        } else {
            // Jika tanggal_referensi KOSONG, set default
            $this->status_arsip = 'AKTIF';
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
        if (empty($keterangan)) return null;
        
        // Cari angka di awal string
        if (preg_match('/^(\d+)/', $keterangan, $matches)) {
            return (int) $matches[1];
        }
        
        return null;
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
}