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
        if (Schema::hasTable('barangs') && !Schema::hasColumn('barangs', 'tanggal')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->date('tanggal')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('barangs') && Schema::hasColumn('barangs', 'tanggal')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->dropColumn('tanggal');
            });
        }
    }
};
