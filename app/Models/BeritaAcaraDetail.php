<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeritaAcaraDetail extends Model
{
    use HasFactory;

    protected $table = 'berita_acara_detail';

    protected $fillable = [
        'bap_id',
        'arsip_id',
        'status', // DIAJUKAN / DIPINDAHKAN
    ];

    // RELASI KE ARSIP
    public function arsip()
    {
        return $this->belongsTo(Arsip::class, 'arsip_id');
    }

    // RELASI KE BAP
    public function bap()
    {
        return $this->belongsTo(BeritaAcaraPindah::class, 'bap_id');
    }
}
