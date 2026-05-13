<?php

namespace App\Http\Controllers;

use App\Models\IncomingBarang;
use App\Models\Barang;
use App\Models\Orders;
use App\Models\Supplier;
use App\Models\MutasiBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomingBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    // ambil semua incoming barang dengan relasi barang & supplier
    $incomingBarangs = IncomingBarang::with(['barang', 'supplier'])->get();

    return view('incoming_barangs.index', compact('incomingBarangs'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil semua barang dan supplier untuk dropdown
        $barangs = Barang::all();
        $suppliers = Supplier::all();

        return view('incoming_barangs.create', compact('barangs', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $request->validate([
        'tgl_masuk'   => 'required|date',
        'barang_id'   => 'required|exists:barangs,id',
        'qty'         => 'required|integer|min:1',
        'harga'       => 'required|numeric',
        'supplier_id' => 'required|exists:suppliers,id',
        'no_sj'       => 'nullable|string',
        'no_invoice'  => 'nullable|string',
        'order_id'    => 'nullable|exists:orders,id'
    ]);

    DB::transaction(function () use ($request) {

        // 1️⃣ Simpan incoming barang
        $incoming = IncomingBarang::create([
            'tgl_masuk'   => $request->tgl_masuk,
            'barang_id'   => $request->barang_id,
            'qty'         => $request->qty,
            'harga'       => $request->harga,
            'supplier_id' => $request->supplier_id,
            'no_sj'       => $request->no_sj,
            'no_invoice'  => $request->no_invoice,
            'order_id'    => $request->order_id,
        ]);

        // 2️⃣ Update stok barang
        $barang = Barang::findOrFail($request->barang_id);
        $barang->stok += $request->qty;
        $barang->save();

        // 3️⃣ Catat mutasi otomatis (IN)
        MutasiBarang::create([
            'tgl_mutasi' => $request->tgl_masuk,
            'barang_id'  => $request->barang_id,
            'qty'        => $request->qty,
            'tipe'       => 'IN',
            'keterangan' => 'Barang masuk - SJ: '.$request->no_sj,
        ]);

        // 4️⃣ (Optional tapi bagus) Update status PO
        if ($request->order_id) {
            $order = Orders::find($request->order_id);
            if ($order) {
                $order->status = 'received';
                $order->save();
            }
        }
    });

    return redirect()
        ->route('incoming-barangs.index')
        ->with('success', 'Barang berhasil masuk & stok otomatis bertambah');
}



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IncomingBarang $incomingBarang)
{
    // kurangi stok barang
    $barang = $incomingBarang->barang;
    $barang->stok -= $incomingBarang->qty;
    $barang->save();

    // hapus record incoming
    $incomingBarang->delete();

    return redirect()->route('incoming-barangs.index')
        ->with('success', 'Incoming barang berhasil dihapus');
}

}
