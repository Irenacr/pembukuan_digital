<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\Service;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        // ======================
        // CHECK TABLE
        // ======================
        $hasTransaksi = Schema::hasTable('transaksis');
        $hasBarang = Schema::hasTable('barangs');
        $hasPembelian = Schema::hasTable('pembelians');
        $hasService = Schema::hasTable('services');

        // ======================
        // DATA UTAMA
        // ======================
        $total_penjualan = 0;
        $hasTotal = $hasTransaksi && Schema::hasColumn('transaksis', 'total');

        if ($hasTotal) {
            try {
                $total_penjualan = Transaksi::sum('total');
            } catch (QueryException $exception) {
                $total_penjualan = 0;
            }
        }

        $jumlah_transaksi = $hasTransaksi
            ? Transaksi::count()
            : 0;

        $stok_rendah = collect();

        if ($hasBarang && Schema::hasColumn('barangs', 'stok')) {
            try {
                $stok_rendah = Barang::where('stok', '<', 5)->get();
            } catch (QueryException $exception) {
                $stok_rendah = collect();
            }
        }

        $transaksi_terbaru = ($hasTransaksi && Schema::hasColumn('transaksis', 'created_at'))
            ? Transaksi::latest()->take(5)->get()
            : collect();

        // ======================
        // SERVICE
        // ======================
        $total_service = ($hasService && Schema::hasColumn('services', 'harga'))
            ? Service::sum('harga')
            : 0;

        // ======================
        // KEUANGAN
        // ======================
        $uang_masuk = $total_penjualan + $total_service;

        $uang_keluar = ($hasPembelian && Schema::hasColumn('pembelians', 'total'))
            ? Pembelian::sum('total')
            : 0;

        $laba_bersih = $uang_masuk - $uang_keluar;

        // ======================
        // GRAFIK (REAL TRANSAKSI)
        // ======================
        $labels = [];
        $data = [];

        if ($hasTotal && Schema::hasColumn('transaksis', 'tanggal')) {

            $grafik = Transaksi::select(
                    DB::raw('YEAR(tanggal) as tahun'),
                    DB::raw('MONTH(tanggal) as bulan'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy('tahun', 'bulan')
                ->orderBy('tahun')
                ->orderBy('bulan')
                ->get();

            $namaBulan = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar',
                4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
                7 => 'Jul', 8 => 'Agu', 9 => 'Sep',
                10 => 'Okt', 11 => 'Nov', 12 => 'Des'
            ];

            foreach ($grafik as $g) {
                $labels[] = $namaBulan[$g->bulan] . ' ' . $g->tahun;
                $data[] = (int) $g->total;
            }
        }

        // ======================
        // RETURN VIEW
        // ======================
        return view('dashboard', compact(
            'total_penjualan',
            'jumlah_transaksi',
            'stok_rendah',
            'transaksi_terbaru',
            'total_service',
            'uang_masuk',
            'uang_keluar',
            'laba_bersih',
            'labels',
            'data'
        ));
    }
}