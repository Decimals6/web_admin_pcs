<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpekPenawaran extends Model
{
    protected $table = 'spek_penawaran';

    protected $fillable = [
        'barang_penawaran_id',
        'nama_spek',
        'keterangan',
        'urutan',
    ];

    // -------------------------------------------------------
    // RELATIONS
    // -------------------------------------------------------

    public function barangPenawaran(): BelongsTo
    {
        return $this->belongsTo(BarangPenawaran::class);
    }
}