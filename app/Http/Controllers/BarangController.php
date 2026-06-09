<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    // 🔹 Tampilkan semua data
    public function index()
    {
        $barangs = Barang::latest()->get(); // lebih rapi (data terbaru di atas)
        return view('barang.index', compact('barangs'));
    }

    // 🔹 Form tambah
    public function create()
    {
        return view('barang.create');
    }

    // 🔹 Simpan data
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'nullable|date',
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'stok' => 'required|numeric|min:0',
            'harga' => 'required|numeric|min:0',
        ]);

        Barang::create($validated);

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil ditambahkan');
    }

    // 🔹 (Optional) detail
    public function show(Barang $barang)
    {
        return view('barang.show', compact('barang'));
    }

    // 🔹 Form edit
    public function edit(Barang $barang)
    {
        return view('barang.edit', compact('barang'));
    }

    // 🔹 Update data
    public function update(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'tanggal' => 'nullable|date',
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'stok' => 'required|numeric|min:0',
            'harga' => 'required|numeric|min:0',
        ]);

        $barang->update($validated);

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil diperbarui');
    }

    // 🔹 Hapus data
    public function destroy(Barang $barang)
    {
        $barang->delete();

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil dihapus');
    }
}