<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomingBarang extends Model
{
    protected $fillable = [
    'tgl_masuk',
    'barang_id',
    'qty',
    'harga',
    'supplier_id',
    'no_sj',
    'no_invoice',
    'order_id'
];


    protected $casts = [
        'tgl_masuk' => 'date',
    ];

    // Relasi ke Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    // Relasi ke Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relasi ke Order (PO)
    public function order()
    {
        return $this->belongsTo(Orders::class);
    }
}
