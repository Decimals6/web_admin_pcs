<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceDetail;
use App\Models\orders;

class Invoice extends Model
{

    const TYPE_MASUK = 'in';
    const TYPE_KELUAR = 'out';
    protected $fillable = [
        'no',
        'no_so',
        'tgl',
        'dpp',
        'ppn',
        'grand_total',
        'jatuh_tempo',
        'status',
        'paid',
        'type', // penjualan / pembelian
        'customer_id',
        'supplier_id',
        'delivery_note_id',
    ];

    protected $casts = [
        'tgl' => 'date',
        'jatuh_tempo' => 'date',
        'dpp' => 'decimal:2',
        'ppn' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function payments()
    {
        return $this->hasMany(PaymentDetail::class, 'invoice_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function deliveryNoteOld()
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function deliveryNote()
    {
        return $this->belongsToMany(
            DeliveryNote::class,
            'invoice_delivery_notes'
        )->withPivot('keterangan')->withTimestamps();
    }
    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id'); // pastikan field foreign key 'order_id' ada di table invoices
    }
    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class);
    }


    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    // Sisa tagihan
    public function getSisaAttribute()
    {
        return $this->grand_total - $this->paid;
    }

    // Cek lunas otomatis
    public function getIsLunasAttribute()
    {
        return $this->paid >= $this->grand_total;
    }
    public function details()
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    //ongkir
    public function ongkirs()
    {
        return $this->hasMany(InvoiceOngkir::class);
    }
    public function getTotalOngkirAttribute()
    {
        return $this->ongkirs->sum('nominal');
    }

    public function getGrandTotalWithOngkirAttribute()
    {
        return $this->grand_total + $this->total_ongkir;
    }

    
}
