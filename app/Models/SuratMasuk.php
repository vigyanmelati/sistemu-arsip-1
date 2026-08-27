<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratMasuk extends Model
{
    protected $table = 'surat_masuk';

    protected $guarded = [];

    protected $casts = [
        'tanggal_dokumen' => 'date',
        'tanggal_penyelesaian' => 'date',
        'bantuan' => 'array',
    ];

    public function subBagian()
    {
        return $this->belongsTo(SubBagian::class);
    }

    public function instansi()
    {
        return $this->belongsTo(SuratInstansi::class, 'instansi_id');
    }

    public function tujuanDisposisis()
    {
        return $this->belongsToMany(TujuanDisposisi::class, 'surat_masuk_tujuan_disposisi')
            ->withTimestamps();
    }

    public function sinarV1Document()
    {
        return $this->belongsTo(SinarV1Document::class);
    }
}
