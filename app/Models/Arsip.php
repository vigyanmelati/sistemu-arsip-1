<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arsip extends Model
{
    protected $table = 'arsips';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'kode_klasifikasi',
        'judul_arsip',
        'sub_bagian',
        'tahun_arsip',
        'nomor_rak',
        'nomor_box',
        'aktif_tahun',
        'inaktif_tahun',
        'tindak_lanjut',
        'status_arsip'
    ];

    /**
     * Relasi ke riwayat arsip
     */
    public function riwayat()
    {
        return $this->hasMany(RiwayatArsip::class, 'arsip_id');
    }
}
