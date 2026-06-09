<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{
    // ================= INDEX =================
    public function index()
    {
        $services = Service::latest()->get();
        return view('service.index', compact('services'));
    }

    // ================= CREATE =================
    public function create()
    {
        return view('service.create');
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_service' => 'required|string|max:255',
            'customer'     => 'required|string|max:255',
            'harga'        => 'required|numeric',
            'status'       => 'required|string',
            'tanggal'      => 'required|date',
        ]);

        Service::create($validated);

        return redirect()->route('service.index')
            ->with('success', 'Service berhasil ditambahkan');
    }

    // ================= EDIT =================
    public function edit($id) // ❗ HAPUS string
    {
        $service = Service::findOrFail($id);
        return view('service.edit', compact('service'));
    }

    // ================= UPDATE =================
    public function update(Request $request, $id) // ❗ HAPUS string
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'nama_service' => 'required|string|max:255',
            'customer'     => 'required|string|max:255',
            'harga'        => 'required|numeric',
            'status'       => 'required|string',
            'tanggal'      => 'required|date',
        ]);

        $service->update($validated);

        return redirect()->route('service.index')
            ->with('success', 'Service berhasil diupdate');
    }

    // ================= DELETE =================
    public function destroy($id) // ❗ HAPUS string
    {
        Service::findOrFail($id)->delete();

        return redirect()->route('service.index')
            ->with('success', 'Service berhasil dihapus');
    }
}