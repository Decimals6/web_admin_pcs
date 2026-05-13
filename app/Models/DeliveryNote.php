<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Invoice;

class DeliveryNote extends Model
{
    protected $casts = [
        'tgl' => 'datetime:Y-m-d', // otomatis jadi Carbon object
    ];
    protected $fillable = [
        'no',
        'type',
        'tgl',
        'keterangan',
        'alamat_kirim',
        'order_id'
    ];

    public function details()
    {
        return $this->hasMany(DeliveryNoteDetail::class);
    }

    public function order()
    {
        return $this->belongsTo(Orders::class);
    }
    // RELASI LAMA
    public function invoicesOld()
    {
        return $this->hasMany(Invoice::class);
    }

    // RELASI BARU
    public function invoices()  
    {
        return $this->belongsToMany(
            Invoice::class,
            'invoice_delivery_notes'
        )->withPivot('keterangan')->withTimestamps();
    }
    public static function booted()
    {
        static::created(function ($deliveryNote) {
            foreach ($deliveryNote->details as $detail) {
                $barang = $detail->barang;
                if ($deliveryNote->type == 'masuk') {
                    $barang->stok += $detail->qty;
                } else {
                    $barang->stok -= $detail->qty;
                }
                $barang->save();
            }
        });
    }


}
