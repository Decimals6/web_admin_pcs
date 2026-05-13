<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Supplier;
use App\Models\IncomingBarang;
use App\Models\MutasiBarang;

class Barang extends Model
{
    protected $table = 'barangs';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'supplier_id',
        'stok'
    ];

    // Relasi ke supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relasi ke IncomingBarang
    public function incoming_barangs()
    {
        return $this->hasMany(IncomingBarang::class);
    }

    // Relasi ke MutasiBarang
    public function mutasi_barangs()
    {
        return $this->hasMany(MutasiBarang::class);
    }
    public function hargas()
    {
        return $this->hasMany(BarangHarga::class)->orderBy('min_qty');
    }

}


