<?php

namespace App\Models;
use App\Models\OrderDetail;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    protected $fillable = ['invoice_id', 'order_detail_id', 'qty', 'subtotal'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class);
    }
}
