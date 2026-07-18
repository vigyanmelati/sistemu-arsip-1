<?php
// app/Models/BeritaAcaraDetail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeritaAcaraDetail extends Model
{
    protected $table = 'berita_acara_detail';
    use HasFactory;
 // Constants status
    const STATUS_BELUM = 'BELUM';
    const STATUS_DIAJUKAN = 'DIAJUKAN';
    const STATUS_DITERIMA = 'DITERIMA';
    const STATUS_DITOLAK = 'DITOLAK';

    protected $fillable = [
        'bap_id',
        'arsip_id',
        'status'
    ];

    // Relationships
    public function beritaAcara()
    {
        return $this->belongsTo(BeritaAcaraPindah::class, 'bap_id');
    }

    public function arsip()
    {
        return $this->belongsTo(Arsip::class, 'arsip_id');
    }
}