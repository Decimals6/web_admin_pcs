<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Barang;

class Supplier extends Model
{
    protected $fillable = [
        'nama_supplier',
        'email',
        'telepon',
        'alamat'
    ];
    
    public function barangs()
    {
    return $this->hasMany(Barang::class);
    }
    public function invoices()
{
    return $this->hasMany(Invoice::class);
}


}
