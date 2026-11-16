<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiBarang extends Model
{
    protected $fillable = [
        'tgl_mutasi',
        'barang_id',
        'delivery_note_detail_id',
        'sampel_id',
        'qty',
        'tipe',
        'sumber',
        'keterangan'
    ];
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
    public function deliveryNoteDetail()
    {
        return $this->belongsTo(DeliveryNoteDetail::class);
    }
    public function sampel()
    {
        return $this->belongsTo(Sampel::class);
    }

}

