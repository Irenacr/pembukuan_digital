<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barangs'; // optional (default Laravel memang ini)

    protected $fillable = [
        'tanggal',
        'nama_barang',
        'kategori',
        'stok',
        'harga'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'stok' => 'integer',
        'harga' => 'integer',
    ];
    public function detailTransaksis()
    {
        return $this->hasMany(DetailTransaksi::class);
    }
}
