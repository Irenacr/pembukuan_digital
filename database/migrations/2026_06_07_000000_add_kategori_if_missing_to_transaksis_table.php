<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transaksis') && !Schema::hasColumn('transaksis', 'kategori')) {
            Schema::table('transaksis', function (Blueprint $table) {
                $table->string('kategori')->nullable()->after('nama_penerima');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transaksis') && Schema::hasColumn('transaksis', 'kategori')) {
            Schema::table('transaksis', function (Blueprint $table) {
                $table->dropColumn('kategori');
            });
        }
    }
};
