<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TujuanDisposisi extends Model
{
    protected $fillable = ['nama_tujuan', 'aktif', 'created_by'];
    protected $casts = ['aktif' => 'boolean'];
}
