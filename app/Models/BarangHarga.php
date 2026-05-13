<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangHarga extends Model
{
    protected $fillable = [
        'barang_id',
        'min_qty',
        'harga'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
