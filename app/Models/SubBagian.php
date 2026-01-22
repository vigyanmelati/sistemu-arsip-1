<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubBagian extends Model
{
    protected $table = 'sub_bagians';
    protected $fillable = ['nama_sub_bagian'];
    
    public function arsips()
    {
        return $this->hasMany(Arsip::class, 'sub_bagian_id');
    }
}