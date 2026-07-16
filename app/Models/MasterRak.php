<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterRak extends Model
{
    protected $table = 'master_raks';

    protected $fillable = [
        'lokasi_arsip',
        'nomor_rak',
        'keterangan',
    ];

    public function boxes()
    {
        return $this->hasMany(MasterBox::class, 'rak_id');
    }

    // MasterBox.php — tambahkan
public function arsips()
{
    return $this->hasMany(Arsip::class, 'box_id');
}
}