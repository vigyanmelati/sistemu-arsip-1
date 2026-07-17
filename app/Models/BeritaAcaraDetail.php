<?php
// app/Models/BeritaAcaraDetail.php

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
        'status', // DRAFT, DIAJUKAN, DITERIMA, DITOLAK
    ];

    public function bap()
    {
        return $this->belongsTo(BeritaAcaraPindah::class, 'bap_id');
    }

    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }
}