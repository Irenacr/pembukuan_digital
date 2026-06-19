<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Barang;
use Illuminate\Http\Request;

class PembelianController extends Controller
{
    // TAMPILKAN DATA
    public function index()
    {
        $pembelians = Pembelian::with('barang')->latest()->get();
        return view('pembelian.index', compact('pembelians'));
    }

    // FORM TAMBAH
    public function create()
    {
        $barangs = Barang::all();
        return view('pembelian.create', compact('barangs'));
    }

    // SIMPAN DATA
    public function store(Request $request)
    {
        // VALIDASI
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:1',
            'harga_beli' => 'required|numeric|min:0',
            'tanggal' => 'required|date_format:Y-m-d'
        ]);

        $barang = Barang::whereRaw('LOWER(nama_barang) = ?', [strtolower(trim($validated['nama_barang']))])->first();

        if (!$barang) {
            $barang = Barang::create([
                'tanggal' => $validated['tanggal'],
                'nama_barang' => trim($validated['nama_barang']),
                'kategori' => null,
                'stok' => 0,
                'harga' => $validated['harga_beli'],
            ]);
        }

        // HITUNG TOTAL
        $total = $validated['jumlah'] * $validated['harga_beli'];

        // SIMPAN
        Pembelian::create([
            'barang_id' => $barang->id,
            'jumlah' => $validated['jumlah'],
            'harga_beli' => $validated['harga_beli'],
            'total' => $total,
            'tanggal' => $validated['tanggal']
        ]);

        //  TAMBAH STOK OTOMATIS
        $barang->stok += $validated['jumlah'];
        $barang->harga = $validated['harga_beli'];
        $barang->save();

        return redirect('/pembelian')->with('success', 'Pembelian berhasil ditambahkan');
    }

    // (sementara kosong, belum dipakai)
    public function show(Pembelian $pembelian) {}

    public function edit(Pembelian $pembelian) 
    {
        $barangs = Barang::all();
        return view('pembelian.edit', compact('pembelian', 'barangs'));
    }

    public function update(Request $request, Pembelian $pembelian) 
    {
        // VALIDASI
        $validated = $request->validate([
            'barang_id' => 'required',
            'jumlah' => 'required|numeric|min:1',
            'harga_beli' => 'required|numeric|min:0',
            'tanggal' => 'required|date_format:Y-m-d'
        ]);

        // HITUNG TOTAL
        $total = $validated['jumlah'] * $validated['harga_beli'];

        // UPDATE
        $pembelian->update([
            'barang_id' => $validated['barang_id'],
            'jumlah' => $validated['jumlah'],
            'harga_beli' => $validated['harga_beli'],
            'total' => $total,
            'tanggal' => $validated['tanggal']
        ]);

        return redirect('/pembelian')->with('success', 'Pembelian berhasil diupdate');
    }

    public function destroy(Pembelian $pembelian) 
    {
        $barang = $pembelian->barang;
        
        if ($barang) {
            $barang->stok -= $pembelian->jumlah;
            $barang->save();
        }

        $pembelian->delete();

        return redirect('/pembelian')->with('success', 'Pembelian berhasil dihapus');
    }
}
