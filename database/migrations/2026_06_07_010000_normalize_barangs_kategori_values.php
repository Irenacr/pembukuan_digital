<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('barangs') && Schema::hasColumn('barangs', 'kategori')) {
            // Normalize common variants
            DB::table('barangs')->where('kategori', 'Barang Baru')->update(['kategori' => 'barang_baru']);
            DB::table('barangs')->where('kategori', 'Barang Bekas')->update(['kategori' => 'barang_bekas']);
            // lowercase other values
            $rows = DB::table('barangs')->whereNotNull('kategori')->get(['id','kategori']);
            foreach ($rows as $row) {
                $val = $row->kategori;
                if (!in_array($val, ['barang_baru','barang_bekas'])) {
                    DB::table('barangs')->where('id', $row->id)->update(['kategori' => strtolower(str_replace(' ', '_', $val))]);
                }
            }
        }
    }

    public function down(): void
    {
        // no-op
    }
};
