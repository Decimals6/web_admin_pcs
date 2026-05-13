<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $fillable = ['tanggal_mulai', 'tanggal_akhir', 'keterangan'];

    // Relasi ke detail absensi harian
    public function detailUsers(): HasMany
    {
        return $this->hasMany(AbsensiUser::class, 'absensi_id');
    }

    // Relasi ke ringkasan premi
    public function premiHadirs(): HasMany
    {
        return $this->hasMany(PremiHadir::class, 'absensi_id');
    }
}