<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable = [
        'order_id',
        'barang_id',
        'harga',
        'qty',
        'hpp',
        'subtotal',
        'keterangan',
        'qty_sent',
    ];

    public function order()
{
    return $this->belongsTo(Orders::class, 'order_id');
}


    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
    public function deliveryNoteDetails()
    {
        return $this->hasMany(DeliveryNoteDetail::class);
    }

}
