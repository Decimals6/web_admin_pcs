<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangPenawaran extends Model
{
    protected $table = 'barang_penawaran';

    protected $fillable = [
        'penawaran_id',
        'barang_id',
        'nama_snapshot',
        'tipe',
        'satuan',
        'urutan',
        'keterangan',
    ];

    // -------------------------------------------------------
    // RELATIONS
    // -------------------------------------------------------

    public function penawaran(): BelongsTo
    {
        return $this->belongsTo(Penawaran::class);
    }

    /**
     * Relasi ke master barang — bisa null kalau barang sudah dihapus,
     * tapi nama_snapshot tetap aman tersimpan.
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function spekPenawaran(): HasMany
    {
        return $this->hasMany(SpekPenawaran::class)->orderBy('urutan');
    }

    public function hargaPenawaran(): HasMany
    {
        return $this->hasMany(HargaPenawaran::class)->orderBy('min_qty');
    }

    // -------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------

    public function isConsumable(): bool
    {
        return $this->tipe === 'consumable';
    }

    public function isEquipment(): bool
    {
        return $this->tipe === 'equipment';
    }

    /**
     * Resolve harga berdasarkan qty yang diminta.
     * Ambil tier tertinggi yang min_qty-nya masih <= qty.
     *
     * Contoh tier: 100→10rb, 200→9rb, 500→7rb
     * resolveHarga(250) → 9rb (tier 200 adalah yang paling cocok)
     */
    public function resolveHarga(int $qty): ?float
    {
        return $this->hargaPenawaran
            ->where('min_qty', '<=', $qty)
            ->sortByDesc('min_qty')
            ->first()
            ?->harga;
    }

    /**
     * Untuk equipment, langsung ambil harga satu-satunya (min_qty=1).
     */
    public function getHargaEquipmentAttribute(): ?float
    {
        return $this->hargaPenawaran->first()?->harga;
    }
}