<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratInstansi extends Model
{
    protected $fillable = [
        'nama_instansi', 'alamat', 'telepon', 'fax', 'email', 'website',
        'aktif', 'created_by',
    ];
    protected $casts = ['aktif' => 'boolean'];

    public function suratMasuks()
    {
        return $this->hasMany(SuratMasuk::class, 'instansi_id');
    }
}
