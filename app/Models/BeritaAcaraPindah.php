<?php
// app/Models/BeritaAcaraPindah.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeritaAcaraPindah extends Model
{
    protected $table = 'berita_acara_pindah';
    
    protected $fillable = [
        'nomor_bap',
        'tanggal_bap',
        'sub_bagian_id',
        'created_by',
        'status',
        'file_bap',
        'tanggal_kirim',
        'tanggal_diterima',
        'diterima_by',
        'tanggal_ditolak',
        'ditolak_by',
        'alasan_ditolak'
    ];

    // Constants
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_DIAJUKAN = 'DIAJUKAN';
    const STATUS_DITERIMA = 'DITERIMA';
    const STATUS_DITOLAK = 'DITOLAK';

    // Relationships
    public function details()
    {
        return $this->hasMany(BeritaAcaraDetail::class, 'bap_id');
    }

    public function subBagian()
    {
        return $this->belongsTo(SubBagian::class, 'sub_bagian_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function diterimaBy()
    {
        return $this->belongsTo(User::class, 'diterima_by');
    }

    public function ditolakBy()
    {
        return $this->belongsTo(User::class, 'ditolak_by');
    }

    // Methods
    public function canEdit()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canDelete()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canSend()
    {
        return $this->status === self::STATUS_DRAFT && $this->details()->count() > 0;
    }

    public function canReceive()
    {
        return $this->status === self::STATUS_DIAJUKAN;
    }
}