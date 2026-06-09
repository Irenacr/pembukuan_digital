<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'tanggal',
        'nama',
        'perusahaan',
        'alamat',
        'kontak'
    ];

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }
}