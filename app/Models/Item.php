<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'nama_barang',
        'jenis_barang',
        'deskripsi'
    ];

    public function images()
    {
        return $this->hasMany(ItemImage::class);
    }
}
