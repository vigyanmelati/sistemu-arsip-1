<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'sub_bagian_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Scope untuk filter role
    public function scopeSuperAdmin($query)
    {
        return $query->where('role', 'super_admin');
    }

    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeRegularUser($query)
    {
        return $query->where('role', 'user');
    }

    // Cek role
    public function roleName()
    {
        return strtolower($this->role ?? '');
    }

    public function isSuperAdmin()
    {
        return $this->roleName() === 'super_admin';
    }

    public function isAdmin()
    {
        return $this->roleName() === 'admin';
    }

    public function isUser()
    {
        return $this->roleName() === 'user';
    }

    public function isTu()
    {
        return $this->roleName() === 'tu';
    }

    // public function canViewRahasiaArsip()
    // {
    //     return in_array($this->roleName(), ['super_admin', 'admin', 'tu'], true);
    // }

    public function canViewRahasiaArsip()
{
    return in_array($this->roleName(), [
        'super_admin',
        'admin',
        'tu'
    ], true);
}

    // public function canViewArsip(Arsip $arsip)
    // {
    //     if ($arsip->klasifikasi_keamanan === 'Rahasia') {
    //         return $this->canViewRahasiaArsip();
    //     }

    //     return true;
    // }

    public function canViewArsip(Arsip $arsip)
    {
        // Biasa/Terbuka
        if ($arsip->klasifikasi_keamanan === 'Biasa/Terbuka') {
            return true;
        }

        // Terbatas
        if ($arsip->klasifikasi_keamanan === 'Terbatas') {
            return true;
        }

        // Rahasia
        if ($arsip->klasifikasi_keamanan === 'Rahasia') {

            // Super admin, admin dan TU boleh
            if (in_array($this->roleName(), ['super_admin', 'admin', 'tu'])) {
                return true;
            }

            // User hanya boleh melihat arsip yang dibuatnya sendiri
            return $arsip->created_by == $this->id;
        }

        return false;
    }

    // public function canDownloadArsip(Arsip $arsip)
    // {
    //     if ($arsip->klasifikasi_keamanan === 'Biasa/Terbuka') {
    //         return true;
    //     }

    //     if ($arsip->klasifikasi_keamanan === 'Terbatas') {
    //         return $this->isAdmin() || $this->isSuperAdmin() || $this->isTu();
    //     }

    //     return $arsip->klasifikasi_keamanan === 'Rahasia' && $this->canViewRahasiaArsip();
    // }

public function canDownloadArsip(Arsip $arsip)
{
    // 1. Biasa/Terbuka → semua boleh
    if ($arsip->klasifikasi_keamanan === 'Biasa/Terbuka') {
        return true;
    }

    // 2. Terbatas
    if ($arsip->klasifikasi_keamanan === 'Terbatas') {
        // Super Admin dan Admin (Unit Kearsipan) tetap bisa semua
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return true;
        }
        // Selain itu, hanya boleh jika sub-bagian user sama dengan sub-bagian arsip
        return $this->sub_bagian_id && $this->sub_bagian_id == $arsip->sub_bagian_id;
    }

    // 3. Rahasia
    if ($arsip->klasifikasi_keamanan === 'Rahasia') {
        // Super Admin, Admin, dan TU bisa semua
        if ($this->isSuperAdmin() || $this->isAdmin() || $this->isTu()) {
            return true;
        }
        // User biasa hanya boleh miliknya sendiri
        return $arsip->created_by == $this->id;
    }

    return false;
}

    public function subBagian()
    {
        return $this->belongsTo(SubBagian::class);
    }

    public function canEditArsip(Arsip $arsip)
{
    // Super admin dan admin selalu bisa
    if ($this->isSuperAdmin() || $this->isAdmin()) {
        return true;
    }

    // User biasa hanya bisa mengedit arsip yang dibuatnya sendiri
    return $this->id == $arsip->created_by;
}

public function canDeleteArsip(Arsip $arsip)
{
    // Sama seperti edit, atau sesuaikan
    return $this->canEditArsip($arsip);
}
public function hasRole($role)
{
    // Jika role berupa string
    if (is_string($role)) {
        return $this->role === $role;
    }
    // Jika role berupa array
    if (is_array($role)) {
        return in_array($this->role, $role);
    }
    return false;
}
}