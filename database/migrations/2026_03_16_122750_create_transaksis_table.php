<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
   {
    Schema::create('transaksis', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id');
        $table->string('nama_barang')->nullable();
        $table->integer('jumlah');
        $table->integer('total');
        $table->date('tanggal')->nullable();
        $table->string('lokasi_pengiriman');
        $table->string('nama_penerima');
        $table->string('status_pembayaran');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
