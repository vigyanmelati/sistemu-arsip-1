<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KodeKlasifikasi extends Model
{
    protected $table = 'kode_klasifikasis';
    protected $fillable = ['kode', 'uraian'];
    
    public function arsips()
    {
        return $this->hasMany(Arsip::class, 'kode_klasifikasi_id');
    }
}