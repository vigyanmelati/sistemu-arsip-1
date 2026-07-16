<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterBox extends Model
{
    protected $table = 'master_box';

    protected $fillable = [
        'rak_id',
        'nomor_box',
        'kapasitas',
        'keterangan',
    ];

    public function rak()
    {
        return $this->belongsTo(MasterRak::class, 'rak_id');
    }

    // MasterBox.php — tambahkan
public function arsips()
{
    return $this->hasMany(Arsip::class, 'box_id');
}
}