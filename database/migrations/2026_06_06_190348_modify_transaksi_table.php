<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {

            $table->dropColumn([
                'barang_id',
                'jumlah',
                'kategori'
            ]);

        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {

            $table->unsignedBigInteger('barang_id');

            $table->integer('jumlah')->default(1);

            $table->string('kategori')->nullable();

        });
    }
};