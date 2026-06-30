<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sampel extends Model
{
    use HasFactory;

    // Definisikan nama tabel jika tidak menggunakan bentuk plural bahasa Inggris
    protected $table = 'sampels';

    protected $fillable = [
        'tanggal',
        'customer_id',
        'keterangan',
    ];

    /**
     * Relasi ke tabel Customer (Satu nota sampel hanya milik satu customer)
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relasi Many-to-Many ke tabel Barang melalui tabel pivot barang_sampel
     */
    public function barangs()
    {
        return $this->belongsToMany(Barang::class, 'barang_sampel', 'sampel_id', 'barang_id')
            ->withPivot('jumlah') // Mengambil kolom tambahan di tabel pivot
            ->withTimestamps();   // Mengaktifkan timestamps jika di pivot ada created_at/updated_at
    }
}