<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNoteDetail extends Model
{
    protected $fillable = [
    'delivery_note_id',
    'order_detail_id',
    'qty',
    'keterangan'
];

public function orderDetail()
{
    return $this->belongsTo(OrderDetail::class, 'order_detail_id');
}

public function deliveryNote()
{
    return $this->belongsTo(DeliveryNote::class, 'delivery_note_id');
}

public function barang()
{
    return $this->belongsTo(Barang::class);
}





}
