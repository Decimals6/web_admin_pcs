<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangHarga;
use Illuminate\Http\Request;

class BarangHargaController extends Controller
{

    public function index($barangId)
    {
        $barang = Barang::findOrFail($barangId);

        $hargas = BarangHarga::where('barang_id', $barangId)
            ->orderBy('min_qty')
            ->get();

        return view('barang_harga.index', compact(
            'barang',
            'hargas'
        ));
    }

    public function store(Request $request, $barangId)
    {

        $request->validate([
            'min_qty' => 'required|integer|min:1',
            'harga' => 'required|numeric|min:0'
        ]);

        BarangHarga::create([
            'barang_id' => $barangId,
            'min_qty' => $request->min_qty,
            'harga' => $request->harga
        ]);

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function destroy($barangId, $id)
    {
        BarangHarga::findOrFail($id)->delete();

        return response()->json([
            'status' => 'success'
        ]);
    }

    // Tambahkan di BarangHargaController.php

    public function getHargaAjax(Request $request)
    {
        $barangId = $request->barang_id;
        $qty = $request->qty;

        // Logic: Ambil harga dengan min_qty TERBESAR yang masih <= qty inputan
        // Contoh: Input 7, Data: 1, 5, 10. Maka ambil yang 5.
        $hargaData = BarangHarga::where('barang_id', $barangId)
            ->where('min_qty', '<=', $qty)
            ->orderBy('min_qty', 'desc')
            ->first();

        // Kalau tidak ketemu (misal input qty 0), ambil harga dengan min_qty paling kecil
        if (!$hargaData) {
            $hargaData = BarangHarga::where('barang_id', $barangId)
                ->orderBy('min_qty', 'asc')
                ->first();
        }

        return response()->json([
            'harga' => $hargaData ? $hargaData->harga : 0
        ]);
    }
}
