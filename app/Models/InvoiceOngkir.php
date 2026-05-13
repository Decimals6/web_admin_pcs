<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceOngkir extends Model
{
    protected $table = 'invoice_ongkirs';

    protected $fillable = [
        'invoice_id',
        'no',
        'nominal',
        'keterangan'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function getIsPaidAttribute()
    {
        return $this->invoice->status === 'paid';
    }
}