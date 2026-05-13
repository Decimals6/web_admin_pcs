<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Supplier;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
    // $barangs = Barang::with('supplier')->get();
    $query = Barang::with('supplier');

    if ($request->search) {
        $query->where(function ($q) use ($request) {
            $q->where('nama_barang', 'like', '%' . $request->search . '%')
              ->orWhere('kode_barang', 'like', '%' . $request->search . '%');
        });
    }

    $barangs = $query->paginate(10)->withQueryString();

    return view('barangs.index', compact('barangs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $suppliers = Supplier::all();
    return view('barangs.create', compact('suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    $request->validate([
        'kode_barang'  => 'required|unique:barangs',
        'nama_barang'  => 'required',
        'supplier_id'  => 'required|exists:suppliers,id',
        'stok'         => 'required|integer|min:0',
    ]);

    Barang::create([
        'kode_barang' => $request->kode_barang,
        'nama_barang' => $request->nama_barang,
        'supplier_id' => $request->supplier_id,
        'stok'        => $request->stok,
    ]);

    return redirect()->route('barang.index')
        ->with('success', 'Barang berhasil ditambahkan');
}
    /**
     * Display the specified resource.
     */
    public function show(Barang $barang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Barang $barang)
    {
    $suppliers = Supplier::all();
    return view('barangs.edit', compact('barang','suppliers'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barang $barang)
    {
    $request->validate([
        'nama_barang' => 'required',
    ]);

    $barang->update($request->all());

    return redirect()->route('barangs.index')
        ->with('success', 'Barang berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barang $barang)
    {
    $barang->delete();

    return redirect()->route('barangs.index')
        ->with('success', 'Barang berhasil dihapus');
    }

}
