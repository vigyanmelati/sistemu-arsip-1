<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SinarV1Document extends Model
{
    public const CATEGORIES = [
        1 => 'Undang-Undang', 2 => 'PERPU', 3 => 'Peraturan Pemerintah',
        4 => 'Peraturan Presiden', 5 => 'Keputusan Presiden', 6 => 'Peraturan Daerah',
        7 => 'Peraturan KPU', 8 => 'PERMEN/KEPMEN', 9 => 'SE/SK/JUKNIS KPU',
        10 => 'SE/SK/JUKNIS KPU Provinsi Bali',
        11 => 'SE/SK/JUKNIS KPU Kabupaten/Kota', 13 => 'Surat Keluar',
    ];

    public const HARDCOPY_STATUSES = [
        'BELUM_DIVERIFIKASI' => 'Belum Diverifikasi', 'DITEMUKAN' => 'Hardcopy Ditemukan',
        'TIDAK_DITEMUKAN' => 'Hardcopy Tidak Ditemukan', 'HANYA_DIGITAL' => 'Hanya Digital',
        'RUSAK' => 'Hardcopy Rusak',
    ];

    public const INTEGRATION_STATUSES = [
        'BELUM_DIPROSES' => 'Belum Diproses', 'SIAP_DIDAFTARKAN' => 'Siap Didaftarkan',
        'SUDAH_JADI_ARSIP' => 'Sudah Menjadi Arsip V2',
        'TIDAK_PERLU_DIARSIPKAN' => 'Tidak Perlu Diarsipkan',
    ];

    protected $guarded = [];

    protected $casts = [
        'legacy_id' => 'integer', 'legacy_category_id' => 'integer',
        'legacy_bagian_id' => 'integer', 'legacy_user_id' => 'integer',
        'tanggal_dokumen' => 'date', 'tanggal_penyelesaian' => 'date',
        'verified_at' => 'datetime', 'legacy_created_at' => 'datetime',
        'legacy_updated_at' => 'datetime',
    ];

    public function subBagian()
    {
        return $this->belongsTo(SubBagian::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if (! $user->isUser()) {
            return $query;
        }

        return $query->where(function (Builder $visible) use ($user) {
            $visible->whereBetween('legacy_category_id', [1, 11])
                ->orWhere(function (Builder $outgoing) use ($user) {
                    $outgoing->where('legacy_category_id', 13)
                        ->where('sub_bagian_id', $user->sub_bagian_id);
                });
        });
    }

    public function isOutgoingLetter(): bool
    {
        return $this->legacy_category_id === 13;
    }
}
