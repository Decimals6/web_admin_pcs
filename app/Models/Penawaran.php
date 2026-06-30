<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Penawaran extends Model
{
    use SoftDeletes;

    protected $table = 'penawaran';

    protected $fillable = [
        'no_penawaran',
        'perihal',
        'tanggal',
        'berlaku_hingga',
        'customer_id',
        'up',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal'        => 'date',
        'berlaku_hingga' => 'date',
    ];

    // -------------------------------------------------------
    // RELATIONS
    // -------------------------------------------------------

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function barangPenawaran(): HasMany
    {
        return $this->hasMany(BarangPenawaran::class)->orderBy('urutan');
    }

    // -------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------

    /**
     * Total nilai penawaran — dijumlah dari harga terendah tiap barang.
     * Cocok untuk estimasi kasar di listing.
     */
    public function getTotalEstimasiAttribute(): float
    {
        return $this->barangPenawaran->sum(function ($item) {
            return $item->hargaPenawaran->min('harga') ?? 0;
        });
    }

    public function isExpired(): bool
    {
        if (! $this->berlaku_hingga) {
            return false;
        }

        return Carbon::parse($this->berlaku_hingga)->isPast();
    }
}