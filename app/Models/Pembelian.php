<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $fillable = [
        'barang_id',
        'jumlah',
        'harga_beli',
        'total',
        'tanggal',
    ];

    // RELASI KE BARANG
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}