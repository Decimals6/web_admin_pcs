<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\Barang;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\DeliveryNote;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrdersController extends Controller
{
    public function index()
    {
        return Orders::with(['customers', 'supplier'])
            ->latest()
            ->get();
    }
    //============================= pembelian ========================================================
    // Tampilkan semua PO

    public function indexPO(Request $request)
    {
        $query = Orders::with(['supplier', 'details.barang'])
            ->where('type', 'purchase'); // kalau kamu pakai type

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('no', 'like', '%' . $request->search . '%')
                    ->orWhereHas('supplier', function ($s) use ($request) {
                        $s->where('nama_supplier', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $orders = $query->orderByDesc('tgl')
            ->paginate(10)
            ->withQueryString();

        return view('pembelian.purchase-order.index', compact('orders'));
    }

    public function createPO()
    {
        $suppliers = Supplier::all();
        $barangs = Barang::all();
        $deliveryNotes = DeliveryNote::where('type', 'masuk')->get(); // <- ini
        return view('pembelian.purchase-order.create', compact('suppliers', 'barangs', 'deliveryNotes'));
    }

    public function storePO(Request $request)
    {
        $request->validate([
            'tgl' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'barang_id.*' => 'required|exists:barangs,id',
            'qty.*' => 'required|numeric|min:0.01',
            'harga.*' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Hitung subtotal tiap barang
            $subtotals = [];
            foreach ($request->barang_id as $i => $barangId) {
                $subtotals[$i] = $request->qty[$i] * $request->harga[$i];
            }

            $dpp = array_sum($subtotals);
            $pajak = $dpp * 0.11; // PPN 11%
            $total = $dpp + $pajak;

            // Simpan ke tabel orders
            $order = Orders::create([
                'no' => $request->no,
                'type' => 'purchase',
                'tgl' => $request->tgl,
                'supplier_id' => $request->supplier_id,
                'customer_id' => null,
                'pajak' => $pajak,
                'dpp' => $dpp,
                'total' => $total,
                'status' => 'Belum Lunas',
                'keterangan' => 'TOP: ' . $request->top . ' | Tgl Kirim: ' . $request->tgl_kirim . ' | ' . $request->keterangan,
            ]);

            // Simpan order_details dan update stok
            foreach ($request->barang_id as $i => $barangId) {
                \App\Models\OrderDetail::create([
                    'order_id' => $order->id,
                    'barang_id' => $barangId,
                    'harga' => $request->harga[$i],
                    'qty' => $request->qty[$i],
                    'hpp' => null,
                    'subtotal' => $subtotals[$i],
                    'keterangan' => null,
                    'qty_sent' => 0,
                ]);

            }

            DB::commit();

            return redirect()->route('pembelian.purchase-order.index')
                ->with('success', 'Purchase Order berhasil dibuat dan stok diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function detail($id)
    {
        $po = Orders::with('details.barang')->findOrFail($id);

        return response()->json($po->details);
    }

    public function showDetailPO($id)
    {
        $po = Orders::with('details.barang')->findOrFail($id);
        return view('pembelian.purchase-order.detail', compact('po'));
    }

    //============================= End pembelian ========================================================

    //============================= PENJUALAN ========================================================
    // Tampilkan semua SO
    // public function indexSO()
    // {
    //     $orders = Orders::with(['customer', 'details.barang'])
    //         ->where('type', 'sales')
    //         ->latest()
    //         ->get();

    //     return view('penjualan.sales-order.index', compact('orders'));
    // }
    public function indexSO(Request $request)
    {
        $query = Orders::with(['customer', 'details.barang'])
            ->where('type', 'sales'); // kalau kamu pakai type

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('no', 'like', '%' . $request->search . '%')
                    ->orWhereHas('customer', function ($c) use ($request) {
                        $c->where('nama_customer', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $orders = $query->orderByDesc('tgl')
            ->paginate(10)
            ->withQueryString();

        return view('penjualan.sales-order.index', compact('orders'));
    }


    public function createSO()
    {
        $suppliers = Supplier::all();
        $customer = Customer::all();
        $barangs = Barang::all();
        return view('penjualan.sales-order.create', compact('customer', 'barangs'));
    }

    public function storeSO(Request $request)
    {
        $request->validate([
            'tgl' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'barang_id.*' => 'required|exists:barangs,id',
            'qty.*' => 'required|numeric|min:0.01',
            'harga.*' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Hitung subtotal tiap barang
            $subtotals = [];
            foreach ($request->barang_id as $i => $barangId) {
                $subtotals[$i] = $request->qty[$i] * $request->harga[$i];
            }

            $dpp = array_sum($subtotals);
            $pajak = $dpp * 0.11; // PPN 11%
            $total = $dpp + $pajak;

            // Simpan ke tabel orders
            $order = Orders::create([
                'no' => $request->no,
                'type' => 'sales',
                'tgl' => $request->tgl,
                'supplier_id' => null,
                'customer_id' => $request->customer_id,
                'pajak' => $pajak,
                'dpp' => $dpp,
                'total' => $total,
                'status' => 'Belum Lunas',
                'keterangan' => 'TOP: ' . $request->top . ' | Tgl Kirim: ' . $request->tgl_kirim . ' | ' . $request->keterangan,
            ]);

            // Simpan order_details dan update stok
            foreach ($request->barang_id as $i => $barangId) {
                \App\Models\OrderDetail::create([
                    'order_id' => $order->id,
                    'barang_id' => $barangId,
                    'harga' => $request->harga[$i],
                    'qty' => $request->qty[$i],
                    'hpp' => null,
                    'subtotal' => $subtotals[$i],
                    'keterangan' => null,
                    'qty_sent' => 0,
                ]);

                
            }

            DB::commit();

            return redirect()->route('penjualan.sales-order.index')
                ->with('success', 'Sales Order berhasil dibuat dan stok diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function detailSO($id)
    {
        $so = Orders::with('details.barang')->findOrFail($id);

        return response()->json($so->details);
    }

    public function showDetailSO($id)
    {
        $po = Orders::with('details.barang')->findOrFail($id);
        return view('penjualan.sales-order.detail', compact('po'));
    }


    //============================= End PENJUALAN ========================================================
}