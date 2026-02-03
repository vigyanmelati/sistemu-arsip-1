<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemusnahanDetail extends Model
{
    protected $table = 'pemusnahan_detail';

    protected $fillable = [
        'pemusnahan_id',
        'arsip_id',
        'keputusan',
        'catatan',
    ];

    // 🔗 detail milik satu pemusnahan
    public function pemusnahan()
    {
        return $this->belongsTo(Pemusnahan::class, 'pemusnahan_id');
    }

    // 🔗 detail milik satu arsip
    public function arsip()
    {
        return $this->belongsTo(Arsip::class, 'arsip_id');
    }
}
