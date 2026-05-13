<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
{
    $query = \App\Models\Supplier::query();

    if ($request->search) {
        $query->where(function ($q) use ($request) {
            $q->where('nama_supplier', 'like', '%' . $request->search . '%')
              ->orWhere('email', 'like', '%' . $request->search . '%')
              ->orWhere('telepon', 'like', '%' . $request->search . '%');
        });
    }

    $suppliers = $query->paginate(10)->withQueryString();

    return view('suppliers.index', compact('suppliers'));
}


    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required'
        ]);

        // Supplier::create($request->all());
        Supplier::create($request->only(
    'nama_supplier',
    'email',
    'telepon',
    'alamat'
));


        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'nama_supplier' => 'required'
        ]);

        $supplier->update($request->all());

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil diupdate');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil dihapus');
    }
}