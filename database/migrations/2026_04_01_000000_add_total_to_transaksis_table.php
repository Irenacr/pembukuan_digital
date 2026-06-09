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
        if (Schema::hasTable('transaksis') && !Schema::hasColumn('transaksis', 'total')) {
            Schema::table('transaksis', function (Blueprint $table) {
                $table->integer('total')->default(0)->after('customer_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transaksis') && Schema::hasColumn('transaksis', 'total')) {
            Schema::table('transaksis', function (Blueprint $table) {
                $table->dropColumn('total');
            });
        }
    }
};
