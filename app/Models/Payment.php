<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'total',
        'keterangan',
        'type',
        'customer_id',
        'supplier_id',
    ];

    public function details()
    {
        return $this->hasMany(PaymentDetail::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}

