<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Satker extends Model
{
    protected $fillable = [
        'nama_satker',
        'kode_satker',
        'alamat',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // helper ambil satker yg lagi aktif
        public static function aktif(): ?self
        {
            return \Cache::remember('satker_aktif', 3600, function () {
                return static::where('is_active', true)->first();
            });
        }

    // tambahkan di App\Models\Satker

    protected static function booted()
    {
        static::saving(function (Satker $satker) {
            if ($satker->is_active) {
                static::where('id', '!=', $satker->id)->update(['is_active' => false]);
            }
        });

        static::saved(fn () => \Cache::forget('satker_aktif'));
        static::deleted(fn () => \Cache::forget('satker_aktif'));
    }
}