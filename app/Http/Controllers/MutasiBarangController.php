<?php

namespace App\Http\Controllers;

use App\Models\MutasiBarang;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MutasiBarangController extends Controller
{

    public function index(Request $request)
    {
        $barangId = $request->barang_id;

        $barangs = Barang::orderBy('nama_barang')->get();

        $mutasis = collect();

        if ($barangId) {

            $saldo = 0;

            $allMutasi = MutasiBarang::where('barang_id', $barangId)
                ->orderBy('tgl_mutasi')
                ->orderBy('id')
                ->get()
                ->map(function ($mutasi) use (&$saldo) {

                    $mutasi->masuk = 0;
                    $mutasi->keluar = 0;

                    if ($mutasi->tipe === 'IN') {
                        $mutasi->masuk = $mutasi->qty;
                        $saldo += $mutasi->qty;
                    }

                    if ($mutasi->tipe === 'OUT') {
                        $mutasi->keluar = $mutasi->qty;
                        $saldo -= $mutasi->qty;
                    }

                    $mutasi->saldo = $saldo;

                    return $mutasi;
                });

            // PAGINATION COLLECTION
            $perPage = 10;
            $currentPage = $request->get('page', 1);

            $items = $allMutasi->slice(($currentPage - 1) * $perPage, $perPage)->values();

            $mutasis = new LengthAwarePaginator(
                $items,
                $allMutasi->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
        }

        return view('mutasi_barangs.index', compact('mutasis', 'barangs', 'barangId'));
    }


    public function create()
    {
        $barangs = Barang::orderBy('nama_barang')->get();

        return view('mutasi_barangs.create', compact('barangs'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'tgl_mutasi' => 'required|date',
            'barang_id' => 'required|exists:barangs,id',
            'qty' => 'required|integer|min:1',
            'tipe' => 'required|in:IN,OUT',
        ]);

        DB::beginTransaction();

        try {

            $barang = Barang::findOrFail($request->barang_id);

            // CEK STOK TIDAK BOLEH MINUS
            if ($request->tipe === 'OUT' && $barang->stok < $request->qty) {
                return back()->withErrors([
                    'qty' => 'Stok tidak cukup. Stok tersedia: ' . $barang->stok
                ])->withInput();
            }

            $mutasi = MutasiBarang::create([
                'tgl_mutasi' => $request->tgl_mutasi,
                'barang_id' => $request->barang_id,
                'qty' => $request->qty,
                'tipe' => $request->tipe,
                'keterangan' => $request->keterangan ?? null,
            ]);

            if ($mutasi->tipe === 'IN') {
                $barang->increment('stok', $mutasi->qty);
            } else {
                $barang->decrement('stok', $mutasi->qty);
            }

            DB::commit();

            return redirect()->route('mutasi-barangs.index')
                ->with('success', 'Mutasi barang berhasil disimpan');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function destroy(MutasiBarang $mutasiBarang)
    {

        DB::beginTransaction();

        try {

            $barang = $mutasiBarang->barang;

            if ($mutasiBarang->tipe === 'IN') {
                $barang->decrement('stok', $mutasiBarang->qty);
            } else {
                $barang->increment('stok', $mutasiBarang->qty);
            }

            $mutasiBarang->delete();

            DB::commit();

            return redirect()->route('mutasi-barangs.index')
                ->with('success', 'Mutasi barang berhasil dihapus');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}