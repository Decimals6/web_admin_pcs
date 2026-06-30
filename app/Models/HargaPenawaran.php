<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaPenawaran extends Model
{
    protected $table = 'harga_penawaran';

    protected $fillable = [
        'barang_penawaran_id',
        'min_qty',
        'harga',
    ];

    protected $casts = [
        'min_qty' => 'integer',
        'harga'   => 'float',
    ];

    // -------------------------------------------------------
    // RELATIONS
    // -------------------------------------------------------

    public function barangPenawaran(): BelongsTo
    {
        return $this->belongsTo(BarangPenawaran::class);
    }
}