<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('transaksis') && !Schema::hasColumn('transaksis', 'nota_text')) {
            Schema::table('transaksis', function (Blueprint $table) {
                $table->text('nota_text')->nullable()->after('nota_scan');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('transaksis') && Schema::hasColumn('transaksis', 'nota_text')) {
            Schema::table('transaksis', function (Blueprint $table) {
                $table->dropColumn('nota_text');
            });
        }
    }
};
