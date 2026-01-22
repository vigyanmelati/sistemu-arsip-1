<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arsip extends Model
{
    protected $table = 'arsips';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'kode_klasifikasi_id',
        'judul_arsip',
        'sub_bagian_id',
        'tahun_arsip',
        'tanggal_arsip',
        'jumlah_berkas',
        'satuan_arsip',
        'tingkat_perkembangan',
        'aktif_tahun',
        'aktif_jra_tahun',
        'inaktif_tahun',
        'inaktif_jra_tahun',
        'keterangan_jra',
        'tanggal_masuk',
        'aktif_sampai',
        'inaktif_sampai',
        'nomor_rak',
        'nomor_box',
        'nomor_sampul',
        'status_arsip',
        'keterangan',
        'file_dokumen',
        'created_by'
    ];

    // Relasi ke KodeKlasifikasi
    public function kodeKlasifikasi()
    {
        return $this->belongsTo(KodeKlasifikasi::class, 'kode_klasifikasi_id');
    }

    // Relasi ke SubBagian
    public function subBagian()
    {
        return $this->belongsTo(SubBagian::class, 'sub_bagian_id');
    }

    // HAPUS DUA ACCESSOR BERIKUT:
    // public function getKodeKlasifikasiAttribute()
    // {
    //     return $this->kodeKlasifikasi ? $this->kodeKlasifikasi->kode : null;
    // }

    // public function getSubBagianNamaAttribute()
    // {
    //     return $this->subBagian ? $this->subBagian->nama : null;
    // }
    
    // Jika tetap ingin punya accessor, gunakan nama yang berbeda:
    // public function getKodeKlasifikasiStrAttribute()
    // {
    //     return $this->kodeKlasifikasi ? $this->kodeKlasifikasi->kode : null;
    // }
}