<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryPindah extends Model
{
     protected $table = 'riwayat_pindah';
    protected $fillable = [
        'arsip_id',
        'dari_rak',
        'dari_box',
        'ke_rak',
        'ke_box',
        'tanggal_pindah',
        'alasan_pindah',
        'user_id',
        'berita_acara_id'
    ];

    protected $dates = ['tanggal_pindah'];

    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function beritaAcara()
    {
        return $this->belongsTo(BeritaAcaraPindah::class, 'berita_acara_id');
    }
}
