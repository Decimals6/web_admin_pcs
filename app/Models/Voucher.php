<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'no',
        'tgl_mulai',
        'tgl_akhir',
        'total'
    ];

    /**
     * Relasi: 1 Voucher punya banyak Kas
     */
    public function kas()
    {
        return $this->hasMany(Kas::class);
    }
}