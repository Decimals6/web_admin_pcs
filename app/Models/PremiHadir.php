<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PremiHadir extends Model
{
    protected $table = 'premi_hadir';
    protected $fillable = [
        'user_id',
        'absensi_id',
        'total_hadir',
        'nominal_premi_harian',
        'nominal_sewa_harian',
        'subtotal_premi',
        'subtotal_sewa',
        'total_keseluruhan',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function absensi(): BelongsTo
    {
        return $this->belongsTo(Absensi::class, 'absensi_id');
    }
}