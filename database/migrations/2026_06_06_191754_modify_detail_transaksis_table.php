<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_transaksis', function (Blueprint $table) {

            $table->foreignId('transaksi_id')
                  ->constrained('transaksis')
                  ->onDelete('cascade');

            $table->foreignId('barang_id')
                  ->constrained('barangs')
                  ->onDelete('cascade');

            $table->integer('qty')->default(1);

            $table->integer('harga_satuan')->default(0);

            $table->integer('diskon')->default(0);

            $table->integer('subtotal')->default(0);

        });
    }

    public function down(): void
    {
        Schema::table('detail_transaksis', function (Blueprint $table) {

            $table->dropForeign(['transaksi_id']);
            $table->dropForeign(['barang_id']);

            $table->dropColumn([
                'transaksi_id',
                'barang_id',
                'qty',
                'harga_satuan',
                'diskon',
                'subtotal'
            ]);

        });
    }
};