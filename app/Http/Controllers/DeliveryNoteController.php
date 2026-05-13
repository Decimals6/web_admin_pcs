<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteDetail;
use App\Models\Orders;
use App\Models\OrderDetail;
use App\Models\Barang;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\MutasiBarang;

class DeliveryNoteController extends Controller
{
    // ===========================
    // INDEX
    // ===========================

    // DN Pembelian (Masuk)
    public function indexMasuk(Request $request)
    {
        $query = DeliveryNote::with(['order.supplier', 'details'])
            ->where('type', 'masuk'); // kalau kamu pakai type

        // 🔎 SEARCH
        if ($request->search) {
            $query->where(function ($q) use ($request) {

                $q->where('no', 'like', '%' . $request->search . '%')

                    ->orWhereHas('order', function ($o) use ($request) {
                        $o->where('no', 'like', '%' . $request->search . '%');
                    })

                    ->orWhereHas('order.supplier', function ($s) use ($request) {
                        $s->where('nama_supplier', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $deliveryNotes = $query->orderByDesc('tgl')
            ->paginate(10)
            ->withQueryString();

        return view('pembelian.delivery_note.index', compact('deliveryNotes'));
    }

    // DN Penjualan (Keluar)
    public function indexKeluar(Request $request)
    {
        $query = DeliveryNote::with(['order.customer', 'details'])
            ->where('type', 'keluar');

        // 🔎 SEARCH
        if ($request->search) {
            $query->where(function ($q) use ($request) {

                $q->where('no', 'like', '%' . $request->search . '%')

                    ->orWhereHas('order', function ($o) use ($request) {
                        $o->where('no', 'like', '%' . $request->search . '%');
                    })

                    ->orWhereHas('order.customer', function ($c) use ($request) {
                        $c->where('nama_customer', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $deliveryNotes = $query->orderByDesc('tgl')
            ->paginate(10)
            ->withQueryString();

        return view('penjualan.delivery_note.index', compact('deliveryNotes'));
    }


    // ===========================
    // CREATE
    // ===========================

    // Form DN Pembelian
    public function createMasuk()
    {
        $orders = Orders::where('type', 'purchase')
            ->where('status', '!=', 'draft')
            ->with('details.barang', 'supplier')
            ->get();

        return view('pembelian.delivery_note.create', compact('orders'));
    }

    // Form DN Penjualan
    public function createKeluar()
    {
        $orders = Orders::where('type', 'sales')
            ->where('status', '!=', 'draft')
            ->with('details.barang', 'customer')
            ->get();

        return view('penjualan.delivery_note.create', compact('orders'));
    }

    // ===========================
    // STORE
    // ===========================

    public function storeMasuk(Request $request, $type)
    {
        $request->validate([
            'no' => 'required|unique:delivery_notes,no',
            'tgl' => 'required|date',
            'alamat_kirim' => 'required',
            'order_id' => 'nullable|exists:orders,id',
            'details.*.order_detail_id' => 'required|exists:order_details,id',
            'details.*.keterangan' => 'nullable|string'
        ]);

        $deliveryNote = DeliveryNote::create([
            'no' => $request->no,
            'type' => $type, // masuk/keluar
            'tgl' => $request->tgl,
            'order_id' => $request->order_id,
            'keterangan' => $request->keterangan,
            'alamat_kirim' => $request->alamat_kirim
        ]);

        foreach ($request->details as $item) {
            $detail = DeliveryNoteDetail::create([
                'delivery_note_id' => $deliveryNote->id,
                'order_detail_id' => $item['order_detail_id'],
                'keterangan' => $item['keterangan'] ?? null
            ]);

            // Update stok otomatis
            $barang = $detail->orderDetail->barang;
            if ($type == 'masuk') {
                $barang->stok += $detail->orderDetail->qty;
            } else {
                $barang->stok -= $detail->orderDetail->qty;
            }
            $barang->save();
        }

        $route = $type == 'masuk' ? 'pembelian.delivery-note.index' : 'penjualan.delivery-note.index';
        return redirect()->route($route)->with('success', 'Delivery Note berhasil dibuat.');
    }

    public function store(Request $request, $type)
    {
        $request->validate([
            'no' => 'required|unique:delivery_notes,no',
            'tgl' => 'required|date',
            'alamat_kirim' => 'required',
            'order_id' => 'nullable|exists:orders,id',
            'details.*.order_detail_id' => 'required|exists:order_details,id',
            'details.*.qty' => 'required|integer|min:1',
            'details.*.keterangan' => 'nullable|string'
        ]);

        $deliveryNote = DeliveryNote::create([
            'no' => $request->no,
            'type' => $type,
            'tgl' => $request->tgl,
            'order_id' => $request->order_id,
            'keterangan' => $request->keterangan,
            'alamat_kirim' => $request->alamat_kirim
        ]);

        foreach ($request->details as $item) {

            $orderDetail = OrderDetail::findOrFail($item['order_detail_id']);

            // // 🔥 Hitung sisa yang boleh dikirim
            // $sisaQty = $orderDetail->qty - $orderDetail->qty_sent;

            // if ($item['qty'] > $sisaQty) {
            //     return back()->withErrors([
            //         'qty' => 'Qty kirim melebihi sisa yang belum dikirim'
            //     ])->withInput();
            // }

            $detail = DeliveryNoteDetail::create([
                'delivery_note_id' => $deliveryNote->id,
                'order_detail_id' => $item['order_detail_id'],
                'qty' => $item['qty'],
                'keterangan' => $item['keterangan'] ?? null
            ]);
            // 🔥 Tambah Mutasi Barang
            MutasiBarang::create([
                'tgl_mutasi' => $deliveryNote->tgl,
                'barang_id' => $orderDetail->barang_id,
                'qty' => $item['qty'],
                'tipe' => $type == 'masuk' ? 'IN' : 'OUT',
                'keterangan' => null
            ]);


            // 🔥 Update qty_sent
            $orderDetail->qty_sent += $item['qty'];
            $orderDetail->save();

            // 🔥 Update stok
            $barang = $orderDetail->barang;

            if ($type == 'masuk') {
                $barang->stok += $item['qty'];
            } else {
                $barang->stok -= $item['qty'];
            }

            $barang->save();
        }

        $route = $type == 'masuk'
            ? 'pembelian.delivery-note.index'
            : 'penjualan.delivery-note.index';

        return redirect()->route($route)
            ->with('success', 'Delivery Note berhasil dibuat.');
    }


    // ===========================
    // EDIT
    // ===========================

    public function edit(DeliveryNote $deliveryNote)
    {
        if ($deliveryNote->type == 'masuk') {
            $orders = Orders::where('type', 'purchase')
                ->where('status', '!=', 'draft')
                ->with('details.barang', 'supplier')
                ->get();

            $deliveryNote->load('details.orderDetail.barang');

            return view('pembelian.delivery_note.edit', compact('deliveryNote', 'orders'));
        } else {
            $orders = Orders::where('type', 'sales')
                ->where('status', '!=', 'draft')
                ->with('details.barang', 'customer')
                ->get();

            $deliveryNote->load('details.orderDetail.barang');

            return view('penjualan.delivery_note.edit', compact('deliveryNote', 'orders'));
        }
    }

    // ===========================
    // SHOW
    // ===========================

    public function show(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load('details.orderDetail.barang', 'order');

        if ($deliveryNote->type == 'masuk') {
            return view('pembelian.delivery_note.show', compact('deliveryNote'));
        } else {
            return view('penjualan.delivery_note.show', compact('deliveryNote'));
        }
    }

    // ===========================
    // UPDATE
    // ===========================

    public function update(Request $request, DeliveryNote $deliveryNote)
    {
        $request->validate([
            'tgl' => 'required|date',
            'details.*.order_detail_id' => 'required|exists:order_details,id',
        ]);

        // Rollback stok lama
        foreach ($deliveryNote->details as $d) {
            $barang = $d->orderDetail->barang;
            if ($deliveryNote->type == 'masuk') {
                $barang->stok -= $d->orderDetail->qty;
            } else {
                $barang->stok += $d->orderDetail->qty;
            }
            $barang->save();
        }

        // Update DN
        $deliveryNote->update([
            'tgl' => $request->tgl,
            'keterangan' => $request->keterangan,
            'alamat_kirim' => $request->alamat_kirim
        ]);

        // Hapus detail lama
        $deliveryNote->details()->delete();

        // Tambah detail baru & update stok
        foreach ($request->details as $item) {
            $detail = DeliveryNoteDetail::create([
                'delivery_note_id' => $deliveryNote->id,
                'order_detail_id' => $item['order_detail_id'],
                'keterangan' => $item['keterangan'] ?? null
            ]);

            $barang = $detail->orderDetail->barang;
            if ($deliveryNote->type == 'masuk') {
                $barang->stok += $detail->orderDetail->qty;
            } else {
                $barang->stok -= $detail->orderDetail->qty;
            }
            $barang->save();
        }

        $route = $deliveryNote->type == 'masuk' ? 'pembelian.delivery-note.index' : 'penjualan.delivery-note.index';
        return redirect()->route($route)->with('success', 'Delivery Note berhasil diupdate.');
    }

    // ===========================
    // DELETE
    // ===========================

    public function destroy(DeliveryNote $deliveryNote)
    {
        // Rollback stok sebelum hapus
        foreach ($deliveryNote->details as $d) {
            $barang = $d->orderDetail->barang;
            if ($deliveryNote->type == 'masuk') {
                $barang->stok -= $d->orderDetail->qty;
            } else {
                $barang->stok += $d->orderDetail->qty;
            }
            $barang->save();
        }

        $deliveryNote->delete();

        $route = $deliveryNote->type == 'masuk' ? 'pembelian.delivery-note.index' : 'penjualan.delivery-note.index';
        return redirect()->route($route)->with('success', 'Delivery Note berhasil dihapus.');
    }

    public function getDeliveryNoteDetail($id)
    {
        $dn = DeliveryNote::with('details.orderDetail.barang')->findOrFail($id);

        $items = $dn->details->map(function ($detail) {
            return [
                'barang_id' => $detail->orderDetail->barang->id,
                'nama_barang' => $detail->orderDetail->barang->nama_barang,
                'qty' => $detail->orderDetail->qty,
            ];
        });

        return response()->json($items);
    }

    // ===========================
    // DETAILS
    // ===========================
    public function showDetailPO($id)
    {
        $dn = DeliveryNote::with([
            'order.supplier',
            'details.orderDetail.barang'
        ])->findOrFail($id);

        return view('pembelian.delivery_note.detail', compact('dn'));
    }

    public function showDetailSO($id)
    {
        $dn = DeliveryNote::with([
            'order.customer',
            'details.orderDetail.barang'
        ])->findOrFail($id);

        return view('penjualan.delivery_note.detail', compact('dn'));
    }


    public function print($id)
    {
        $dn = DeliveryNote::with([
            'order.customer',
            'details.orderDetail.barang'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('penjualan.delivery_note.print', compact('dn'));

        $filename = 'SuratJalan-' . str_replace(['/', '\\'], '-', $dn->no) . '.pdf';

        return $pdf->stream($filename);
    }
    public function printDot($id)
    {
        $dn = DeliveryNote::with([
            'order.customer',
            'details.orderDetail.barang'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('penjualan.delivery_note.printDot', compact('dn'));

        $filename = 'SuratJalan-' . str_replace(['/', '\\'], '-', $dn->no) . '.pdf';

        return $pdf->stream($filename);
    }

    //ADDITIONAL
    public function getOrderDetails($id)
    {
        $order = Orders::with('details.barang')
            ->findOrFail($id);

        return response()->json($order->details);
    }

}
