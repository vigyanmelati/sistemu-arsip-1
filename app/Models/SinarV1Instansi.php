<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SinarV1Instansi extends Model
{
    protected $guarded = [];
    protected $casts = ['legacy_created_at' => 'datetime', 'legacy_updated_at' => 'datetime'];
}
