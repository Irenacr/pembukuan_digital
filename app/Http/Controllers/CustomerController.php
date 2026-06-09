<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
   {
    $customers = Customer::all();
    return view('customer.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
   {
    return view('customer.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
   {
    $validated = $request->validate([
        'tanggal' => 'nullable|date_format:Y-m-d',
        'nama' => 'required|string|max:255',
        'perusahaan' => 'nullable|string|max:255',
        'alamat' => 'required|string',
        'kontak' => 'required|string|max:100',
    ]);

    Customer::create($validated);

    return redirect('/customer')->with('success', 'Customer berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customer.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'tanggal' => 'nullable|date_format:Y-m-d',
            'nama' => 'required|string|max:255',
            'perusahaan' => 'nullable|string|max:255',
            'alamat' => 'required|string',
            'kontak' => 'required|string|max:100',
        ]);

        $customer->update($validated);

        return redirect('/customer')->with('success', 'Customer berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect('/customer')->with('success', 'Customer berhasil dihapus');
    }
}
