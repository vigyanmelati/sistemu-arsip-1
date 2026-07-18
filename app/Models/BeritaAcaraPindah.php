<?php
// app/Models/BeritaAcaraPindah.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeritaAcaraPindah extends Model
{
    protected $table = 'berita_acara_pindah';
    
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_DIAJUKAN = 'DIAJUKAN';
    const STATUS_DISETUJUI = 'DISETUJUI';
    const STATUS_DITOLAK = 'DITOLAK';

    protected $fillable = [
        'nomor_bap',
        'tanggal_bap',
        'sub_bagian_id',
        'created_by',
        'status',
        'file_bap',
        'tanggal_kirim',
        'tanggal_disetujui',
        'disetujui_by',
        'alasan_ditolak',
        'tanggal_ditolak',
        'ditolak_by'
    ];

    protected $casts = [
        'tanggal_bap' => 'date',
        'tanggal_kirim' => 'datetime',
        'tanggal_disetujui' => 'datetime',
        'tanggal_ditolak' => 'datetime',
    ];

    // Relationships
    public function details()
    {
        return $this->hasMany(BeritaAcaraDetail::class, 'bap_id');
    }

    public function subBagian()
    {
        return $this->belongsTo(SubBagian::class, 'sub_bagian_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function disetujuiBy()
    {
        return $this->belongsTo(User::class, 'disetujui_by');
    }

    public function ditolakBy()
    {
        return $this->belongsTo(User::class, 'ditolak_by');
    }

    // Methods
    public function canSend()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canEdit()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canDelete()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canApprove()
    {
        return $this->status === self::STATUS_DIAJUKAN;
    }

    /**
     * Update status BAP berdasarkan status arsip di dalamnya
     */
    public function updateStatusFromArsip()
    {
        $details = $this->details()->with('arsip')->get();
        
        if ($details->isEmpty()) {
            return;
        }

        $statuses = $details->pluck('arsip.status_pindah')->unique()->values();
        
        // Jika semua arsip sudah DITERIMA
        if ($statuses->count() === 1 && $statuses->first() === 'DITERIMA') {
            $this->status = self::STATUS_DISETUJUI;
            $this->tanggal_disetujui = now();
            $this->disetujui_by = auth()->id();
            $this->save();
            return;
        }
        
        // Jika ada arsip yang DITOLAK
        if ($statuses->contains('DITOLAK')) {
            $this->status = self::STATUS_DITOLAK;
            $this->tanggal_ditolak = now();
            $this->ditolak_by = auth()->id();
            $this->save();
            return;
        }
        
        // Jika ada arsip yang DIAJUKAN (belum semua selesai)
        if ($statuses->contains('DIAJUKAN')) {
            $this->status = self::STATUS_DIAJUKAN;
            $this->save();
            return;
        }
    }
}