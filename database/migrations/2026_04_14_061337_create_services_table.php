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
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            // DATA SERVICE
            $table->string('nama_service');   // nama jasa
            $table->string('customer');       // nama customer
            $table->integer('harga');         // harga service
            $table->string('status');         // proses / selesai
            $table->date('tanggal');          // tanggal service

            $table->timestamps();             // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};