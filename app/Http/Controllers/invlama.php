<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\Invoice;
use App\Models\OrderDetail;
use App\Models\Barang;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteDetail;

class InvoiceController extends Controller
{
    // ===========================
    // INDEX
    // ===========================

    public function indexMasuk()
    {
        $invoices = Invoice::with(['supplier', 'deliveryNote.order'])
            ->where('type', 'in') // sesuai enum DB
            ->latest()
            ->get();

        return view('pembelian.invoice.index', compact('invoices'));
    }

    public function indexKeluar()
    {
        $invoices = Invoice::with('details.orderDetail.barang', 'deliveryNote.order.customer')
            ->where('type', 'out') // sesuai enum DB
            ->latest()
            ->get();

        return view('penjualan.invoice.index', compact('invoices'));
    }

    // ===========================
    // CREATE
    // ===========================

    public function createMasuk()
    {
        $suppliers = \App\Models\Supplier::all();
        $deliveryNotes = DeliveryNote::where('type','masuk')->with('order.supplier')->get();
        $orderDetails = OrderDetail::with('barang')->get();

        return view('pembelian.invoice.create', compact('suppliers','deliveryNotes','orderDetails'));
    }

    public function createKeluar()
    {
        $customers = \App\Models\Customer::all();
        $deliveryNotes = DeliveryNote::where('type','keluar')->with('order.customer')->get();
        $orderDetails = OrderDetail::with('barang')->get();

        return view('penjualan.invoice.create', compact('customers','deliveryNotes','orderDetails'));
    }

    // ===========================
    // STORE
    // ===========================

    public function storeMasuk(Request $request)
    {
        $request->validate([
            'no' => 'required|unique:invoices,no',
            'tgl' => 'required|date',
            'jatuh_tempo' => 'required|date',
            'delivery_note_id' => 'required|exists:delivery_notes,id',
            'details.*.order_detail_id' => 'required|exists:order_details,id',
            'details.*.qty' => 'required|numeric|min:1',
            'details.*.harga' => 'required|numeric|min:0',
        ]);

        $dn = DeliveryNote::with('order.supplier')->findOrFail($request->delivery_note_id);
        $supplier_id = $dn->order->supplier->id ?? null;
        $no_so = $dn->order->no ?? null;

        $dpp = collect($request->details)->sum(fn($item) => $item['qty'] * $item['harga']);
        $pajak = $dpp * 0.11;
        $total = $dpp + $pajak;

        $invoice = Invoice::create([
            'no' => $request->no,
            'no_so' => $no_so,
            'tgl' => $request->tgl,
            'jatuh_tempo' => $request->jatuh_tempo,
            'delivery_note_id' => $dn->id,
            'customer_id' => null,
            'supplier_id' => $supplier_id,
            'dpp' => $dpp,
            'ppn' => $pajak,
            'grand_total' => $total,
            'status' => 'unpaid',
            'type' => 'in',
        ]);

        foreach($request->details as $item){
            $invoice->details()->create([
                'order_detail_id' => $item['order_detail_id'],
                'subtotal' => $item['qty'] * $item['harga'],
            ]);
        }

        return redirect()->route('pembelian.invoice.index')
            ->with('success','Invoice berhasil dibuat.');
    }

    public function storeKeluar(Request $request)
    {
        $request->validate([
            'no' => 'required|unique:invoices,no',
            'tgl' => 'required|date',
            'jatuh_tempo' => 'required|date',
            'delivery_note_id' => 'required|exists:delivery_notes,id',
            'details.*.order_detail_id' => 'required|exists:order_details,id',
            'details.*.qty' => 'required|numeric|min:1',
            'details.*.harga' => 'required|numeric|min:0',
        ]);

        $dn = DeliveryNote::with('order.customer')->findOrFail($request->delivery_note_id);
        $customer_id = $dn->order->customer->id ?? null;
        $no_so = $dn->order->no ?? null;

        $dpp = collect($request->details)->sum(fn($item) => $item['qty'] * $item['harga']);
        $pajak = $dpp * 0.11;
        $total = $dpp + $pajak;

        $invoice = Invoice::create([
            'no' => $request->no,
            'no_so' => $no_so,
            'tgl' => $request->tgl,
            'jatuh_tempo' => $request->jatuh_tempo,
            'delivery_note_id' => $dn->id,
            'customer_id' => $customer_id,
            'supplier_id' => null,
            'dpp' => $dpp,
            'ppn' => $pajak,
            'grand_total' => $total,
            'status' => 'unpaid',
            'type' => 'out',
        ]);

        foreach($request->details as $item){
            $invoice->details()->create([
                'order_detail_id' => $item['order_detail_id'],
                'subtotal' => $item['qty'] * $item['harga'],
            ]);
        }

        return redirect()->route('penjualan.invoice.index')
            ->with('success','Invoice berhasil dibuat.');
    }

    // ===========================
    // EDIT
    // ===========================

    public function edit(Invoice $invoice)
    {
        if ($invoice->type == 'in') {
            $orders = Orders::where('type', 'purchase')
                ->where('status', '!=', 'draft')
                ->with('details.barang', 'supplier')
                ->get();
            $invoice->load('details.orderDetail.barang');

            return view('pembelian.invoice.edit', compact('invoice', 'orders'));
        } else {
            $orders = Orders::where('type', 'sales')
                ->where('status', '!=', 'draft')
                ->with('details.barang', 'customer')
                ->get();
            $invoice->load('details.orderDetail.barang');

            return view('penjualan.invoice.edit', compact('invoice', 'orders'));
        }
    }

    // ===========================
    // SHOW
    // ===========================

    public function show(Invoice $invoice)
    {
        $invoice->load('details.orderDetail.barang', 'deliveryNote.order');

        if ($invoice->type == 'in') {
            return view('pembelian.invoice.show', compact('invoice'));
        } else {
            return view('penjualan.invoice.show', compact('invoice'));
        }
    }

    // ===========================
    // UPDATE
    // ===========================

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'tgl' => 'required|date',
            'details.*.order_detail_id' => 'required|exists:order_details,id',
        ]);

        foreach ($invoice->details as $d) {
            $barang = $d->orderDetail->barang;
            $qty = $d->orderDetail->qty;
            if ($invoice->type == 'in') {
                $barang->stok -= $qty;
            } else {
                $barang->stok += $qty;
            }
            $barang->save();
        }

        $invoice->update([
            'tgl' => $request->tgl,
            'keterangan' => $request->keterangan,
            'alamat_kirim' => $request->alamat_kirim
        ]);

        $invoice->details()->delete();

        foreach ($request->details as $item) {
            $detail = $invoice->details()->create([
                'order_detail_id' => $item['order_detail_id'],
                'keterangan' => $item['keterangan'] ?? null
            ]);

            $barang = $detail->orderDetail->barang;
            $qty = $detail->orderDetail->qty;
            if ($invoice->type == 'in') {
                $barang->stok += $qty;
            } else {
                $barang->stok -= $qty;
            }
            $barang->save();
        }

        $route = $invoice->type == 'in' ? 'pembelian.invoice.index' : 'penjualan.invoice.index';
        return redirect()->route($route)->with('success', 'Invoice berhasil diupdate.');
    }

    // ===========================
    // DELETE
    // ===========================

    public function destroy(Invoice $invoice)
    {
        foreach ($invoice->details as $d) {
            $barang = $d->orderDetail->barang;
            $qty = $d->orderDetail->qty;
            if ($invoice->type == 'in') {
                $barang->stok -= $qty;
            } else {
                $barang->stok += $qty;
            }
            $barang->save();
        }

        $invoice->delete();

        $route = $invoice->type == 'in' ? 'pembelian.invoice.index' : 'penjualan.invoice.index';
        return redirect()->route($route)->with('success', 'Invoice berhasil dihapus.');
    }

    // ===========================
    // GET ORDER DETAILS
    // ===========================

    public function getOrderDetail($id)
    {
        $order = Orders::with('details.barang')->findOrFail($id);

        $items = $order->details->map(function ($detail) {
            return [
                'barang_id' => $detail->barang->id,
                'nama_barang' => $detail->barang->nama_barang,
                'qty' => $detail->qty,
            ];
        });

        return response()->json($items);
    }

    public function getDeliveryNoteDetail($id)
    {
        $dn = DeliveryNote::with('details.orderDetail.barang','order.customer','order.supplier')->findOrFail($id);

        $items = $dn->details->map(fn($d) => [
            'barang_id' => $d->orderDetail->barang->id,
            'order_detail_id' => $d->orderDetail->id,
            'nama_barang' => $d->orderDetail->barang->nama_barang,
            'qty' => $d->orderDetail->qty,
            'supplier_name' => $dn->order->supplier->nama_supplier ?? '',
            'customer_name' => $dn->order->customer->nama_customer ?? '',
        ]);

        return response()->json($items);
    }
}
