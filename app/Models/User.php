<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * Helper Check Role
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isStaff()
    {
        return $this->role === 'staff';
    }

    // Tambahkan helper isSales untuk logika sewa kendaraan
    public function isSales()
    {
        // Karena role sudah varchar, kita cek apakah ada kata 'sales' di dalamnya
        return str_contains(strtolower($this->role), 'sales');
    }

    /**
     * Relasi untuk Fitur Absensi & Premi
     */

    // Log kehadiran harian
    public function absensiUsers(): HasMany
    {
        return $this->hasMany(AbsensiUser::class);
    }

    // Settingan nominal sewa kendaraan per user
    public function sewaKendaraan(): HasOne
    {
        return $this->hasOne(SewaKendaraan::class);
    }

    // Hasil akhir kalkulasi premi per sesi
    public function premiHadirs(): HasMany
    {
        return $this->hasMany(PremiHadir::class);
    }

    public function premiUser(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PremiUser::class);
    }

    /**
     * Standard Laravel Attributes
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}