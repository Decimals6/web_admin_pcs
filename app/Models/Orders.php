<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
        const TYPE_MASUK = 'masuk';
    const TYPE_KELUAR = 'keluar';
    protected $casts = [
    'tgl' => 'date',
];

    protected $fillable = [
        'no',
        'type',
        'tgl',
        'pajak',
        'dpp',
        'total',
        'keterangan',
        'status',
        'customer_id',
        'supplier_id',
    ];

    public function details()
{
    return $this->hasMany(OrderDetail::class, 'order_id');
}


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function incomingBarangs()
{
    return $this->hasMany(IncomingBarang::class);
}

}
