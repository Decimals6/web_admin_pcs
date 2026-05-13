<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kas extends Model
{
    protected $table = 'kas';

    protected $fillable = [
        'tanggal',
        'no_transaksi',
        'keterangan',
        'debit',
        'kredit',
        'saldo',
        'jenis',
        'voucher_id'
    ];
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}

