<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $fillable = [
    'customer_id',
    'nama_barang',
    'jumlah',
    'kategori',
    'total',
    'diskon_transaksi',
    'tanggal',
    'lokasi_pengiriman',
    'nama_penerima',
    'status_pembayaran',
    'metode_pembayaran',
    'nota_scan',
    'nota_text',
];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function detailTransaksis()
    {
        return $this->hasMany(DetailTransaksi::class);
    }
}