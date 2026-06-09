<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transaksis')) {

            Schema::table('transaksis', function (Blueprint $table) {

                if (Schema::hasColumn('transaksis', 'barang_id')) {
                    $table->dropColumn('barang_id');
                }

                if (Schema::hasColumn('transaksis', 'jumlah')) {
                    $table->dropColumn('jumlah');
                }

                if (Schema::hasColumn('transaksis', 'kategori')) {
                    $table->dropColumn('kategori');
                }

            });

        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transaksis')) {

            Schema::table('transaksis', function (Blueprint $table) {

                if (!Schema::hasColumn('transaksis', 'barang_id')) {
                    $table->unsignedBigInteger('barang_id')->nullable();
                }

                if (!Schema::hasColumn('transaksis', 'jumlah')) {
                    $table->integer('jumlah')->default(1);
                }

                if (!Schema::hasColumn('transaksis', 'kategori')) {
                    $table->string('kategori')->nullable();
                }

            });

        }
    }
};