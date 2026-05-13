<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemusnahan extends Model
{
    protected $table = 'pemusnahan';

    protected $fillable = [
        'nomor_usulan',
        'tahun',
        'tanggal_usulan',
        'status',
        'keterangan',
         'file_persetujuan_anri', // ✅ WAJIB ADA
    'tanggal_persetujuan_anri',
    'file_persetujuan_kpu',
    'file_berita_acara',
    'file_sk_pemusnahan',
    'tanggal_pemusnahan',

    ];

    protected $casts = [
        'dokumen_pemusnahan' => 'array',
    ];


    // 🔗 satu pemusnahan punya banyak arsip (detail)
    public function details()
    {
        return $this->hasMany(PemusnahanDetail::class, 'pemusnahan_id');
    }


    // 🔍 helper: hanya arsip yang disetujui
    public function arsipDisetujui()
    {
        return $this->details()->where('keputusan', 'disetujui');
    }
}
