<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('barangs') && !Schema::hasColumn('barangs', 'kategori')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->string('kategori')->nullable()->after('nama_barang');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('barangs') && Schema::hasColumn('barangs', 'kategori')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->dropColumn('kategori');
            });
        }
    }
};
