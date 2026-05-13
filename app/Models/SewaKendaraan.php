<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SewaKendaraan extends Model
{
    protected $table = 'sewa_kendaraan';
    protected $fillable = ['user_id', 'nopol', 'nominal'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}