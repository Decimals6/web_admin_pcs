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
use Illuminate\Support\Facades\DB;

class DeliveryNoteController extends Controller
{
    // ===========================
    // INDEX
    // ===========================

    // DN Pembelian (Masuk)
    public function indexMasuk(Request $request)
    {
        $query = DeliveryNote::with(['order.supplier', 'details'])
            ->where('type', 'masuk');

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

    public function createMasuk()
    {
        $orders = Orders::where('type', 'purchase')
            ->where('status', '!=', 'draft')
            ->with('details.barang', 'supplier')
            ->orderByDesc('tgl')
            ->orderByDesc('id')
            ->get();

        return view('pembelian.delivery_note.create', compact('orders'));
    }

    public function createKeluar()
    {
        $orders = Orders::where('type', 'sales')
            ->where('status', '!=', 'draft')
            ->with('details.barang', 'customer')
            ->orderByDesc('tgl')
            ->orderByDesc('id')
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
            'type' => $type,
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
            'details' => 'required|array|min:1',
            'details.*.order_detail_id' => 'required|exists:order_details,id',
            'details.*.qty' => 'required|integer|min:1',
            'details.*.keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $deliveryNote = DeliveryNote::create([
                'no' => $request->no,
                'type' => $type,
                'tgl' => $request->tgl,
                'order_id' => $request->order_id,
                'keterangan' => $request->keterangan,
                'alamat_kirim' => $request->alamat_kirim
            ]);

            $deliveryNote->load('order.supplier', 'order.customer');
            $partnerName = $type == 'masuk'
                ? ($deliveryNote->order->supplier->nama_supplier ?? '-')
                : ($deliveryNote->order->customer->nama_customer ?? '-');
            $prefixKet = $type == 'masuk' ? 'SJ Pembelian ' : 'SJ Penjualan ';
            $keteranganMutasi = $prefixKet . $deliveryNote->no . ' - ' . $partnerName;

            foreach ($request->details as $item) {
                $orderDetail = OrderDetail::with('barang')->findOrFail($item['order_detail_id']);

                $detail = DeliveryNoteDetail::create([
                    'delivery_note_id' => $deliveryNote->id,
                    'order_detail_id' => $item['order_detail_id'],
                    'qty' => $item['qty'],
                    'keterangan' => $item['keterangan'] ?? null
                ]);

                MutasiBarang::create([
                    'tgl_mutasi' => $deliveryNote->tgl,
                    'barang_id' => $orderDetail->barang_id,
                    'delivery_note_detail_id' => $detail->id,
                    'qty' => $item['qty'],
                    'tipe' => $type == 'masuk' ? 'IN' : 'OUT',
                    'sumber' => $type == 'masuk' ? 'pembelian' : 'penjualan',
                    'keterangan' => $keteranganMutasi,
                ]);

                $orderDetail->qty_sent += $item['qty'];
                $orderDetail->save();

                $barang = $orderDetail->barang;
                if ($type == 'masuk') {
                    $barang->stok += $item['qty'];
                } else {
                    $barang->stok -= $item['qty'];
                }
                $barang->save();
            }

            DB::commit();

            $route = $type == 'masuk'
                ? 'pembelian.delivery-note.index'
                : 'penjualan.delivery-note.index';

            return redirect()->route($route)
                ->with('success', 'Delivery Note berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Gagal menyimpan Delivery Note: ' . $e->getMessage())->withInput();
        }
    }

    // ===========================
    // EDIT
    // ===========================

    public function edit(DeliveryNote $deliveryNote)
    {
        $orders = Orders::where('type', 'purchase')
            ->where('status', '!=', 'draft')
            ->with('details.barang', 'supplier')
            ->get();

        $deliveryNote->load('details.orderDetail.barang');

        return view('pembelian.delivery_note.edit', compact('deliveryNote', 'orders'));
    }

    public function editKeluar(DeliveryNote $deliveryNote)
    {
        $orders = Orders::where('type', 'sales')
            ->where('status', '!=', 'draft')
            ->with('details.barang', 'customer')
            ->get();

        $deliveryNote->load('details.orderDetail.barang');

        return view('penjualan.delivery_note.edit', compact('deliveryNote', 'orders'));
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
            'no' => 'required|unique:delivery_notes,no,' . $deliveryNote->id,
            'tgl' => 'required|date',
            'alamat_kirim' => 'required',
            'order_id' => 'nullable|exists:orders,id',
            'details' => 'required|array|min:1',
            'details.*.order_detail_id' => 'required|exists:order_details,id',
            'details.*.qty' => 'required|integer|min:1',
            'details.*.keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $deliveryNote->load('details.orderDetail.barang');
            $oldDetailIds = $deliveryNote->details->pluck('id');

            // === STEP 1: KEMBALIKAN/RESET STOK DAN MUTASI LAMA ===
            foreach ($deliveryNote->details as $oldDetail) {
                $orderDetail = $oldDetail->orderDetail;
                if ($orderDetail) {
                    $orderDetail->qty_sent = max(0, $orderDetail->qty_sent - $oldDetail->qty);
                    $orderDetail->save();

                    if ($orderDetail->barang) {
                        $barang = $orderDetail->barang;
                        $barang->stok -= $oldDetail->qty;
                        $barang->save();
                    }
                }
            }

            MutasiBarang::whereIn('delivery_note_detail_id', $oldDetailIds)->delete();
            $deliveryNote->details()->delete();

            // === STEP 2: UPDATE DATA INDUK DELIVERY NOTE ===
            $deliveryNote->update([
                'no' => $request->no,
                'tgl' => $request->tgl,
                'order_id' => $request->order_id,
                'keterangan' => $request->keterangan,
                'alamat_kirim' => $request->alamat_kirim
            ]);

            $deliveryNote->load('order.supplier');
            $supplierName = $deliveryNote->order->supplier->nama_supplier ?? '-';
            $keteranganMutasi = 'SJ Pembelian ' . $deliveryNote->no . ' - ' . $supplierName;

            // === STEP 3: MASUKKAN DATA BARU ===
            foreach ($request->details as $item) {
                $orderDetail = OrderDetail::with('barang')->findOrFail($item['order_detail_id']);

                $detail = DeliveryNoteDetail::create([
                    'delivery_note_id' => $deliveryNote->id,
                    'order_detail_id' => $item['order_detail_id'],
                    'qty' => $item['qty'],
                    'keterangan' => $item['keterangan'] ?? null
                ]);

                MutasiBarang::create([
                    'tgl_mutasi' => $deliveryNote->tgl,
                    'barang_id' => $orderDetail->barang_id,
                    'delivery_note_detail_id' => $detail->id,
                    'qty' => $item['qty'],
                    'tipe' => 'IN',
                    'sumber' => 'pembelian',
                    'keterangan' => $keteranganMutasi,
                ]);

                $orderDetail->qty_sent += $item['qty'];
                $orderDetail->save();

                $barang = $orderDetail->barang;
                $barang->stok += $item['qty'];
                $barang->save();
            }

            DB::commit();

            return redirect()->route('pembelian.delivery-note.index')
                ->with('success', 'Delivery Note berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Gagal memperbarui Delivery Note: ' . $e->getMessage())->withInput();
        }
    }

    public function updateKeluar(Request $request, DeliveryNote $deliveryNote)
    {
        $request->validate([
            'no' => 'required|unique:delivery_notes,no,' . $deliveryNote->id,
            'tgl' => 'required|date',
            'alamat_kirim' => 'required',
            'order_id' => 'nullable|exists:orders,id',
            'details' => 'required|array|min:1',
            'details.*.order_detail_id' => 'required|exists:order_details,id',
            'details.*.qty' => 'required|integer|min:1',
            'details.*.keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $deliveryNote->load('details.orderDetail.barang');
            $oldDetailIds = $deliveryNote->details->pluck('id');

            // === STEP 1: KEMBALIKAN/RESET STOK DAN MUTASI LAMA (PENJUALAN = OUT, STOK DIKEMBALIKAN +) ===
            foreach ($deliveryNote->details as $oldDetail) {
                $orderDetail = $oldDetail->orderDetail;
                if ($orderDetail) {
                    $orderDetail->qty_sent = max(0, $orderDetail->qty_sent - $oldDetail->qty);
                    $orderDetail->save();

                    if ($orderDetail->barang) {
                        $barang = $orderDetail->barang;
                        $barang->stok += $oldDetail->qty;
                        $barang->save();
                    }
                }
            }

            MutasiBarang::whereIn('delivery_note_detail_id', $oldDetailIds)->delete();
            $deliveryNote->details()->delete();

            // === STEP 2: UPDATE DATA INDUK DELIVERY NOTE ===
            $deliveryNote->update([
                'no' => $request->no,
                'tgl' => $request->tgl,
                'order_id' => $request->order_id,
                'keterangan' => $request->keterangan,
                'alamat_kirim' => $request->alamat_kirim
            ]);

            $deliveryNote->load('order.customer');
            $customerName = $deliveryNote->order->customer->nama_customer ?? '-';
            $keteranganMutasi = 'SJ Penjualan ' . $deliveryNote->no . ' - ' . $customerName;

            // === STEP 3: MASUKKAN DATA BARU (PENJUALAN = OUT, POTONG STOK -) ===
            foreach ($request->details as $item) {
                $orderDetail = OrderDetail::with('barang')->findOrFail($item['order_detail_id']);

                $detail = DeliveryNoteDetail::create([
                    'delivery_note_id' => $deliveryNote->id,
                    'order_detail_id' => $item['order_detail_id'],
                    'qty' => $item['qty'],
                    'keterangan' => $item['keterangan'] ?? null
                ]);

                MutasiBarang::create([
                    'tgl_mutasi' => $deliveryNote->tgl,
                    'barang_id' => $orderDetail->barang_id,
                    'delivery_note_detail_id' => $detail->id,
                    'qty' => $item['qty'],
                    'tipe' => 'OUT',
                    'sumber' => 'penjualan',
                    'keterangan' => $keteranganMutasi,
                ]);

                $orderDetail->qty_sent += $item['qty'];
                $orderDetail->save();

                $barang = $orderDetail->barang;
                $barang->stok -= $item['qty'];
                $barang->save();
            }

            DB::commit();

            return redirect()->route('penjualan.delivery-note.index')
                ->with('success', 'Delivery Note berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Gagal memperbarui Delivery Note: ' . $e->getMessage())->withInput();
        }
    }

    // ===========================
    // DELETE
    // ===========================

    public function destroy(DeliveryNote $deliveryNote)
    {
        DB::beginTransaction();

        try {
            $deliveryNote->load('details.orderDetail.barang');
            $detailIds = $deliveryNote->details->pluck('id');

            // 1. Rollback stok barang & qty_sent
            foreach ($deliveryNote->details as $d) {
                $orderDetail = $d->orderDetail;
                if ($orderDetail) {
                    $qty = $d->qty ?? $orderDetail->qty;
                    $orderDetail->qty_sent = max(0, $orderDetail->qty_sent - $qty);
                    $orderDetail->save();

                    if ($orderDetail->barang) {
                        $barang = $orderDetail->barang;
                        if ($deliveryNote->type == 'masuk') {
                            $barang->stok -= $qty;
                        } else {
                            $barang->stok += $qty;
                        }
                        $barang->save();
                    }
                }
            }

            // 2. Hapus catatan mutasi barang milik detail SJ ini
            MutasiBarang::whereIn('delivery_note_detail_id', $detailIds)->delete();

            // 3. Lepas relasi pivot Many-to-Many ke tabel Invoices
            if (method_exists($deliveryNote, 'invoices')) {
                $deliveryNote->invoices()->detach();
            }

            // 4. Hapus detail item surat jalan
            $deliveryNote->details()->delete();

            // 5. Hapus record utama surat jalan
            $type = $deliveryNote->type;
            $deliveryNote->delete();

            DB::commit();

            $route = $type == 'masuk' ? 'pembelian.delivery-note.index' : 'penjualan.delivery-note.index';
            return redirect()->route($route)->with('success', 'Delivery Note dan dependensinya berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal menghapus Delivery Note: ' . $e->getMessage());
        }
    }

    public function checkRelations(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['details.orderDetail.barang', 'invoices']);

        $stockImpactText = ($deliveryNote->type == 'masuk')
            ? 'Pengurangan stok gudang'
            : 'Pengembalian stok gudang';

        return response()->json([
            'details_count' => $deliveryNote->details->count(),
            'invoices_count' => $deliveryNote->invoices->count(),
            'stock_impact' => $stockImpactText,
        ]);
    }

    public function getDeliveryNoteDetail($id)
    {
        $dn = DeliveryNote::with('details.orderDetail.barang')->findOrFail($id);

        $items = $dn->details->map(function ($detail) {
            return [
                'barang_id' => $detail->orderDetail->barang->id,
                'nama_barang' => $detail->orderDetail->barang->nama_barang,
                'qty' => $detail->qty ?? $detail->orderDetail->qty,
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

    // ADDITIONAL
    public function getOrderDetails($id)
    {
        $order = Orders::with('details.barang')->findOrFail($id);
        return response()->json($order->details);
    }
}
