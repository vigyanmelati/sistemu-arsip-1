<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeritaAcaraPindah extends Model
{
    use HasFactory;

    protected $table = 'berita_acara_pindah';

    protected $fillable = [
        'nomor_bap',
        'tanggal_bap',
        'sub_bagian_id',
        'created_by',
        'file_bap',
        'status', // DIAJUKAN / DISETUJUI
    ];

    // RELASI KE DETAIL
    public function details()
    {
        return $this->hasMany(BeritaAcaraDetail::class, 'bap_id');
    }

    // RELASI KE SUB BAGIAN
    public function subBagian()
    {
        return $this->belongsTo(SubBagian::class, 'sub_bagian_id');
    }

    // RELASI KE USER YANG MEMBUAT
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
