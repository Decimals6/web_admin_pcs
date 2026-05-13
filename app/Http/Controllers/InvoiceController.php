<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\Orders;
use App\Models\Invoice;
use App\Models\OrderDetail;
use App\Models\Supplier;
use App\Models\DeliveryNote;
use App\Models\Customer;
use App\Models\InvoiceOngkir;
use App\Models\Kas;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    // ===========================
    // INDEX
    // ===========================

    public function indexMasuk(Request $request)
    {
        $query = Invoice::with(['supplier'])
            ->where('type', 'in');

        // 🔎 Filter tanggal
        if ($request->filled('from')) {
            $query->whereDate('tgl', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('tgl', '<=', $request->to);
        }

        // 🔎 Filter supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $invoices = $query->latest()
            ->paginate(10)
            ->withQueryString();

        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('pembelian.invoice.index', compact(
            'invoices',
            'suppliers'
        ));
    }



    public function indexKeluar(Request $request)
    {
        // ================= QUERY DASAR =================
        $query = Invoice::with([
            'customer',
            'details.orderDetail'
        ])->where('type', 'out');


        // ================= FILTER TANGGAL =================
        if ($request->filled('from')) {
            $query->whereDate('tgl', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('tgl', '<=', $request->to);
        }


        // ================= FILTER CUSTOMER =================
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }


        // ================= TOTAL KESELURUHAN (SESUAI FILTER) =================
        $totalAllDpp = (clone $query)->sum('dpp');
        $totalAllPpn = (clone $query)->sum('ppn');
        $totalAllGrand = (clone $query)->sum('grand_total');


        // ================= PAGINATION =================
        $invoices = $query->latest()
            ->paginate(10)
            ->withQueryString();


        // ================= DATA CUSTOMER =================
        $customers = Customer::orderBy('nama_customer')->get();


        // ================= RETURN VIEW =================
        return view('penjualan.invoice.index', [
            'invoices' => $invoices,
            'customers' => $customers,
            'totalAllDpp' => $totalAllDpp,
            'totalAllPpn' => $totalAllPpn,
            'totalAllGrand' => $totalAllGrand,
        ]);
    }

    // ===========================
    // DATA PEMBELIAN
    // ===========================

    public function dataPembelian(Request $request)
    {
        $query = Invoice::with([
            'supplier',
            'details.orderDetail'
        ])->where('type', Invoice::TYPE_MASUK);

        // Filter tanggal
        if ($request->from && $request->to) {
            $query->whereBetween('tgl', [
                Carbon::parse($request->from),
                Carbon::parse($request->to)
            ]);
        }

        // Filter supplier
        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // 🔎 Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('no', 'like', '%' . $request->search . '%')
                    ->orWhere('keterangan', 'like', '%' . $request->search . '%')
                    ->orWhereHas('supplier', function ($s) use ($request) {
                        $s->where('nama_supplier', 'like', '%' . $request->search . '%');
                    });
            });
        }

        // Clone query untuk summary (agar tidak kepotong pagination)
        $summaryQuery = clone $query;

        // Pagination
        $invoices = $query->latest()
            ->paginate(10)
            ->withQueryString();

        // Summary dari SEMUA hasil filter (bukan cuma 10 data)
        $totalDpp = $summaryQuery->sum('dpp');
        $totalPpn = $summaryQuery->sum('ppn');
        $grandTotal = $summaryQuery->sum('grand_total');

        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('pembelian.data-pembelian.index', compact(
            'invoices',
            'totalDpp',
            'totalPpn',
            'grandTotal',
            'suppliers'
        ));
    }

    public function dataPenjualan(Request $request)
    {
        $query = Invoice::with([
            'customer',
            'details.orderDetail',
            'paymentDetails.payment'
        ])->where('type', Invoice::TYPE_KELUAR);

        // Filter tanggal
        if ($request->from && $request->to) {
            $query->whereBetween('tgl', [
                Carbon::parse($request->from),
                Carbon::parse($request->to)
            ]);
        }

        // Filter customer
        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        // 🔎 Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('no', 'like', '%' . $request->search . '%')
                    ->orWhere('keterangan', 'like', '%' . $request->search . '%')
                    ->orWhereHas('customer', function ($c) use ($request) {
                        $c->where('nama_customer', 'like', '%' . $request->search . '%');
                    });
            });
        }

        // Clone query untuk summary
        $summaryQuery = clone $query;

        // Pagination
        $invoices = $query->orderBy('tgl', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Summary total sesuai filter
        $totalDpp = $summaryQuery->sum('dpp');
        $totalPpn = $summaryQuery->sum('ppn');
        $grandTotal = $summaryQuery->sum('grand_total');

        $customers = Customer::orderBy('nama_customer')->get();

        return view('penjualan.data-penjualan.index', compact(
            'invoices',
            'totalDpp',
            'totalPpn',
            'grandTotal',
            'customers'
        ));
    }
    //modal data pembelian
    public function getPayments($id)
    {
        $invoice = Invoice::with('paymentDetails.payment')->findOrFail($id);

        $data = [];

        foreach ($invoice->paymentDetails as $pd) {

            $payment = $pd->payment;

            $ket = $payment->keterangan ?? '';

            // Ambil metode dari keterangan
            if (str_contains($ket, 'TF')) {
                $metode = 'Transfer';
            } elseif (str_contains($ket, 'Cash')) {
                $metode = 'Cash';
            } else {
                $metode = '-';
            }

            $data[] = [
                'tgl' => $payment->created_at->format('d-m-Y'),
                'nominal' => number_format($pd->subtotal, 0, ',', '.'),
                'metode' => $metode
            ];
        }

        return response()->json($data);
    }

    // public function dataPenjualan(Request $request)
// {
//     $query = Invoice::with([
//         'customer',
//         'details.orderDetail'
//     ])->where('type','out');

    //     // ================= FILTER TANGGAL =================
//     if ($request->filled('from')) {
//         $query->whereDate('tgl','>=',$request->from);
//     }

    //     if ($request->filled('to')) {
//         $query->whereDate('tgl','<=',$request->to);
//     }

    //     // ================= FILTER CUSTOMER =================
//     if ($request->filled('customer_id')) {
//         $query->where('customer_id',$request->customer_id);
//     }

    //     // ================= TOTAL KESELURUHAN =================
//     $totalAllDpp   = (clone $query)->sum('dpp');
//     $totalAllPpn   = (clone $query)->sum('ppn');
//     $totalAllGrand = (clone $query)->sum('grand_total');

    //     // ================= PAGINATION =================
//     $invoices = $query->latest()
//                       ->paginate(10)
//                       ->withQueryString();

    //     $customers = Customer::orderBy('nama_customer')->get();

    //     return view('penjualan.data-penjualan.index', [
//         'invoices'      => $invoices,
//         'customers'     => $customers,
//         'totalAllDpp'   => $totalAllDpp,
//         'totalAllPpn'   => $totalAllPpn,
//         'totalAllGrand' => $totalAllGrand,
//     ]);
// }
//modal data penjualan
    public function getPaymentsPiutang($id)
    {
        $invoice = Invoice::with('paymentDetails.payment')->findOrFail($id);

        $data = [];

        foreach ($invoice->paymentDetails as $pd) {

            $payment = $pd->payment;

            // pastikan hanya payment type IN
            if ($payment->type !== 'in')
                continue;

            $ket = $payment->keterangan ?? '';

            if (str_contains($ket, 'TF')) {
                $metode = 'Transfer';
            } elseif (str_contains($ket, 'Cash')) {
                $metode = 'Cash';
            } else {
                $metode = '-';
            }

            $data[] = [
                'tgl' => $payment->created_at->format('d-m-Y'),
                'nominal' => number_format($pd->subtotal, 0, ',', '.'),
                'metode' => $metode
            ];
        }

        return response()->json($data);
    }

    public function exportPenjualan(Request $request)
    {
        $query = Invoice::with('customer', 'paymentDetails.payment')
            ->where('type', Invoice::TYPE_KELUAR);

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('tgl', [$request->from, $request->to]);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $invoices = $query->get();

        $filename = "laporan_penjualan.xls";

        $headers = [
            "Content-Type" => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        return response()->view(
            'penjualan.data-penjualan.excel',
            compact('invoices'),
            200,
            $headers
        );
    }
    public function printPenjualan(Request $request)
    {
        $query = Invoice::with('customer', 'paymentDetails.payment')
            ->where('type', Invoice::TYPE_KELUAR);

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('tgl', [$request->from, $request->to]);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $invoices = $query->latest()->get();

        $totalDpp = $invoices->sum('dpp');
        $totalPpn = $invoices->sum('ppn');
        $grandTotal = $invoices->sum('grand_total');

        return view('penjualan.data-penjualan.print', compact(
            'invoices',
            'totalDpp',
            'totalPpn',
            'grandTotal'
        ));
    }

    public function exportPembelian(Request $request)
    {
        $query = Invoice::with('supplier', 'paymentDetails.payment')
            ->where('type', Invoice::TYPE_MASUK);

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('tgl', [$request->from, $request->to]);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $invoices = $query->get();

        $filename = "laporan_pembelian.xls";

        $headers = [
            "Content-Type" => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        return response()->view(
            'pembelian.data-pembelian.excel',
            compact('invoices'),
            200,
            $headers
        );
    }

    public function printPembelian(Request $request)
    {
        $query = Invoice::with('supplier', 'paymentDetails.payment')
            ->where('type', Invoice::TYPE_MASUK);

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('tgl', [$request->from, $request->to]);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $invoices = $query->latest()->get();

        $totalDpp = $invoices->sum('dpp');
        $totalPpn = $invoices->sum('ppn');
        $grandTotal = $invoices->sum('grand_total');

        return view('pembelian.data-pembelian.print', compact(
            'invoices',
            'totalDpp',
            'totalPpn',
            'grandTotal'
        ));
    }


    // ===========================
    // LAPORAN HUTANG
    // ===========================

    public function laporanHutang(Request $request)
    {
        $suppliers = Supplier::with([
            'invoices' => function ($q) {
                $q->where('type', Invoice::TYPE_MASUK);
            },
            'invoices.paymentDetails'
        ])->get();

        return view('pembelian.hutang.index', compact('suppliers'));
    }
    public function getHutangDetail($supplierId)
    {
        $invoices = Invoice::with('paymentDetails')
            ->where('supplier_id', $supplierId)
            ->where('type', Invoice::TYPE_MASUK)
            ->get();

        $data = $invoices->map(function ($inv) {

            $paid = $inv->paymentDetails->sum('subtotal');
            $sisa = $inv->grand_total - $paid;

            if ($sisa <= 0)
                return null;

            return [
                'tgl' => $inv->tgl->format('d-m-Y'),
                'no' => $inv->no,
                'no_so' => $inv->no_so,
                'jatuh_tempo' => $inv->jatuh_tempo->format('d-m-Y'),
                'total' => number_format($inv->grand_total, 0, ',', '.'),
                'paid' => number_format($paid, 0, ',', '.'),
                'sisa' => number_format($sisa, 0, ',', '.')
            ];
        })->filter()->values();

        return response()->json($data);
    }
    
    public function bayarHutang(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'jumlah_bayar' => 'required|numeric|min:1',
            'metode' => 'required'
        ]);
    
        DB::beginTransaction();
    
        try {
            $supplierId = $request->supplier_id;
            $sisaUang = (float) $request->jumlah_bayar; // Pakai float biar presisi pas hitung sisa
    
            // 1. Ambil invoice yang belum lunas
            $invoices = Invoice::with(['paymentDetails', 'ongkirs'])
                ->where('supplier_id', $supplierId)
                ->where('type', Invoice::TYPE_MASUK)
                ->where('status', '!=', 'paid')
                ->orderBy('tgl', 'asc')
                ->get();
    
            // 2. Hitung total sisa hutang real (termasuk ongkir)
            $totalSisaHutang = 0;
            foreach ($invoices as $inv) {
                $paid = $inv->paymentDetails->sum('subtotal');
                $totalTagihan = $inv->grand_total + $inv->ongkirs->sum('nominal');
                $totalSisaHutang += ($totalTagihan - $paid);
            }
    
            // VALIDASI: Beri toleransi 1 rupiah/unit untuk selisih pembulatan
            // Hanya throw error kalau beneran kelebihan bayar lebih dari 1
            if (($sisaUang - $totalSisaHutang) > 1) {
                throw new \Exception('Jumlah bayar melebihi total hutang.');
            }
    
            // 3. Buat header payment
            $payment = Payment::create([
                'total' => $request->jumlah_bayar,
                'keterangan' => 'Pelunasan Hutang - ' . $request->metode,
                'type' => 'out',
                'supplier_id' => $supplierId,
            ]);
    
            // 4. ====== FIFO LOGIC WITH TOLERANCE ======
            foreach ($invoices as $invoice) {
                if ($sisaUang <= 0) break;
    
                $sudahDibayar = $invoice->paymentDetails->sum('subtotal');
                $totalTagihan = $invoice->grand_total + $invoice->ongkirs->sum('nominal');
                $kurang = $totalTagihan - $sudahDibayar;
    
                if ($kurang <= 0) continue;
    
                // Tentukan berapa yang dibayarkan ke invoice ini
                $bayar = min($sisaUang, $kurang);
    
                PaymentDetail::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'subtotal' => $bayar
                ]);
    
                $invoice->paid += $bayar;
    
                // PENGECEKAN STATUS: Toleransi selisih di bawah 1 (koma-komaan)
                // Kalau sisa hutang di invoice ini kurang dari 1, langsung set lunas
                if (($totalTagihan - $invoice->paid) < 1) {
                    $invoice->status = 'paid';
                    $invoice->paid = $totalTagihan; // Force biar angkanya genap lunas di DB
                } else {
                    $invoice->status = 'partial';
                }
    
                $invoice->save();
                $sisaUang -= $bayar;
            }
    
            DB::commit();
            return back()->with('success', 'Pembayaran berhasil disimpan.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage());
        }
    }

    // public function bayarHutang(Request $request)
    // {
    //     $request->validate([
    //         'supplier_id' => 'required|exists:suppliers,id',
    //         'jumlah_bayar' => 'required|numeric|min:1',
    //         'metode' => 'required'
    //     ]);

    //     DB::beginTransaction();

    //     try {

    //         $supplierId = $request->supplier_id;
    //         $sisaUang = (int) $request->jumlah_bayar;

    //         // Ambil invoice yg masih ada sisa, urut paling lama
    //         $invoices = Invoice::with('paymentDetails')
    //             ->where('supplier_id', $supplierId)
    //             ->where('type', Invoice::TYPE_MASUK)
    //             ->orderBy('tgl', 'asc')
    //             ->get();

    //         // Hitung total sisa hutang
    //         $totalSisa = 0;

    //         foreach ($invoices as $inv) {
    //             $paid = $inv->paymentDetails->sum('subtotal');
    //             $kurang = $inv->grand_total - $paid;

    //             if ($kurang > 0) {
    //                 $totalSisa += $kurang;
    //             }
    //         }

    //         if (($sisaUang - $totalSisa) > 1) {
    //             throw new \Exception('Jumlah bayar melebihi total hutang.');
    //         }

    //         // Buat header payment
    //         $payment = Payment::create([
    //             'total' => $request->jumlah_bayar,
    //             'keterangan' => 'Pelunasan Hutang - ' . $request->metode,
    //             'type' => 'out',
    //             'supplier_id' => $supplierId,
    //             'customer_id' => null,
    //         ]);

    //         // ====== FIFO LOGIC ======
    //         foreach ($invoices as $invoice) {

    //             if ($sisaUang <= 0)
    //                 break;

    //             $sudahDibayar = $invoice->paymentDetails->sum('subtotal');

    //             $totalTagihan = $invoice->grand_total + $invoice->ongkirs->sum('nominal');

    //             $kurang = $totalTagihan - $sudahDibayar;

    //             if ($kurang <= 0)
    //                 continue;

    //             $bayar = min($sisaUang, $kurang);

    //             PaymentDetail::create([
    //                 'payment_id' => $payment->id,
    //                 'invoice_id' => $invoice->id,
    //                 'subtotal' => $bayar
    //             ]);

    //             $invoice->paid += $bayar;

    //             if ($invoice->paid >= $totalTagihan) {
    //                 $invoice->status = 'paid';
    //             } else {
    //                 $invoice->status = 'partial';
    //             }

    //             $invoice->save();

    //             $sisaUang -= $bayar;
    //         }

    //         DB::commit();

    //         return back()->with('success', 'Pembayaran berhasil disimpan.');

    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return back()->withErrors($e->getMessage());
    //     }
    // }

    // ===========================
    // LAPORAN PIUTANG
    // ===========================

    public function laporanPiutang()
    {
        $customers = Customer::with([
            'invoices' => function ($q) {
                $q->where('type', 'out'); // invoice penjualan
            },
            'invoices.paymentDetails'
        ])->get();

        return view('penjualan.piutang.index', compact('customers'));
    }

    public function getPiutangDetail($customerId)
    {
        $invoices = Invoice::with('paymentDetails')
            ->where('customer_id', $customerId)
            ->where('type', 'out')
            ->orderBy('tgl', 'asc')
            ->get();

        $data = collect();

        foreach ($invoices as $inv) {

            $paid = $inv->paymentDetails->sum('subtotal');

            $barangPaid = min($paid, $inv->grand_total);
            $barangSisa = $inv->grand_total - $barangPaid;

            $sisaPayment = max(0, $paid - $inv->grand_total);

            if ($barangSisa > 0) {
                $data->push([
                    'tgl' => $inv->tgl->format('d-m-Y'),
                    'no' => $inv->no,
                    'no_so' => $inv->no_so,
                    'jatuh_tempo' => optional($inv->jatuh_tempo)->format('d-m-Y'),
                    'total' => number_format($inv->grand_total, 0, ',', '.'),
                    'paid' => number_format($barangPaid, 0, ',', '.'),
                    'sisa' => number_format($barangSisa, 0, ',', '.'),
                ]);
            }

            foreach ($inv->ongkirs as $ongkir) {

                $ongkirPaid = min($sisaPayment, $ongkir->nominal);
                $ongkirSisa = $ongkir->nominal - $ongkirPaid;

                if ($ongkirSisa > 0) {

                    $data->push([
                        'tgl' => $inv->tgl->format('d-m-Y'),
                        'no' => 'ONGKIR - ' . ($ongkir->no ?? '-'),
                        'no_so' => $inv->no,
                        'jatuh_tempo' => optional($inv->jatuh_tempo)->format('d-m-Y'),
                        'total' => number_format($ongkir->nominal, 0, ',', '.'),
                        'paid' => number_format($ongkirPaid, 0, ',', '.'),
                        'sisa' => number_format($ongkirSisa, 0, ',', '.'),
                    ]);

                }

                $sisaPayment -= $ongkirPaid;
            }
        }

        return response()->json($data->values());
    }

    public function bayarPiutang(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'jumlah_bayar' => 'required|numeric|min:1',
            'metode' => 'required'
        ]);

        DB::beginTransaction();

        try {

            $customerId = $request->customer_id;
            $sisaUang = (int) $request->jumlah_bayar;

            $invoices = Invoice::with(['paymentDetails', 'ongkirs'])
                ->where('customer_id', $customerId)
                ->where('type', 'out')
                ->orderBy('tgl', 'asc')
                ->get();

            $totalSisa = 0;

            foreach ($invoices as $inv) {

                $paid = $inv->paymentDetails->sum('subtotal');

                $totalTagihan = $inv->grand_total_with_ongkir;

                $kurang = $totalTagihan - $paid;

                if ($kurang > 0) {
                    $totalSisa += $kurang;
                }
            }

            if ($sisaUang > $totalSisa) {
                throw new \Exception('Jumlah bayar melebihi total piutang.');
            }

            $payment = Payment::create([
                'total' => $request->jumlah_bayar,
                'keterangan' => 'Pelunasan Piutang - ' . $request->metode,
                'type' => 'in',
                'customer_id' => $customerId,
                'supplier_id' => null,
            ]);

            foreach ($invoices as $invoice) {

                if ($sisaUang <= 0)
                    break;

                $sudahDibayar = $invoice->paymentDetails->sum('subtotal');

                $totalTagihan = $invoice->grand_total_with_ongkir;

                $kurang = $totalTagihan - $sudahDibayar;

                if ($kurang <= 0)
                    continue;

                $bayar = min($sisaUang, $kurang);

                PaymentDetail::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'subtotal' => $bayar
                ]);

                $invoice->paid += $bayar;

                if ($invoice->paid >= $totalTagihan) {
                    $invoice->status = 'paid';
                } else {
                    $invoice->status = 'partial';
                }

                $invoice->save();

                $sisaUang -= $bayar;
            }

            DB::commit();

            return back()->with('success', 'Pembayaran piutang berhasil disimpan.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->withErrors($e->getMessage());
        }
    }


    // ===========================
    // CREATE
    // ===========================

    public function createMasuk()
    {
        $suppliers = Supplier::all();
        $deliveryNotes = DeliveryNote::where('type', 'masuk')->with('order.supplier')->get();
        $orderDetails = OrderDetail::with('barang')->get();

        return view('pembelian.invoice.create', compact('suppliers', 'deliveryNotes', 'orderDetails'));
    }

    public function createKeluar()
    {
        $customers = Customer::all();
        $deliveryNotes = DeliveryNote::where('type', 'keluar')->with('order.customer')->get();
        $orderDetails = OrderDetail::with('barang')->get();

        return view('penjualan.invoice.create', compact('customers', 'deliveryNotes', 'orderDetails'));
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
            'delivery_note_ids' => 'required|array|min:1',
            'delivery_note_ids.*' => 'exists:delivery_notes,id',
            'details.*.order_detail_id' => 'required|exists:order_details,id',
            'details.*.qty' => 'required|numeric|min:1',
            'details.*.harga' => 'required|numeric|min:0',
            'ppn_mode' => 'required|in:ppn,non',
        ]);

        DB::beginTransaction();

        try {

            $dns = DeliveryNote::with('order.supplier')
                ->whereIn('id', $request->delivery_note_ids)
                ->get();

            if ($dns->isEmpty()) {
                throw new \Exception('Delivery Note tidak ditemukan.');
            }

            $supplier_id = $dns->first()->order->supplier->id ?? null;
            $no_so = $dns->first()->order->no ?? null;

            foreach ($dns as $dn) {
                if (($dn->order->supplier->id ?? null) != $supplier_id) {
                    throw new \Exception('Semua Delivery Note harus dari supplier yang sama.');
                }
            }

            $dpp = collect($request->details)
                ->sum(fn($item) => $item['qty'] * $item['harga']);

            $mode = $request->ppn_mode;

            $ppn = 0;

            if ($mode === 'ppn') {
                $ppn = $dpp * 0.11;
            }

            $total = $dpp + $ppn;

            $invoice = Invoice::create([
                'no' => $request->no,
                'no_so' => $no_so,
                'tgl' => $request->tgl,
                'jatuh_tempo' => $request->jatuh_tempo,
                'customer_id' => null,
                'supplier_id' => $supplier_id,
                'dpp' => $dpp,
                'ppn' => $ppn,
                'grand_total' => $total,
                'status' => 'unpaid',
                'paid' => 0,
                'type' => Invoice::TYPE_MASUK,
            ]);

            // pivot
            $invoice->deliveryNote()->attach($request->delivery_note_ids);

            foreach ($request->details as $item) {
                $invoice->details()->create([
                    'order_detail_id' => $item['order_detail_id'],
                    'qty' => $item['qty'],
                    'subtotal' => $item['qty'] * $item['harga'],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('pembelian.invoice.index')
                ->with('success', 'Invoice berhasil dibuat.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withErrors($e->getMessage())
                ->withInput();
        }
    }

    public function storeKeluar(Request $request)
    {
        $request->validate([
            'tgl' => 'required|date',
            'jatuh_tempo' => 'required|date',
            'delivery_note_ids' => 'required|array|min:1',
            'delivery_note_ids.*' => 'exists:delivery_notes,id',
            'details' => 'required|array|min:1',
            'details.*.order_detail_id' => 'required|exists:order_details,id',
            'details.*.qty' => 'required|numeric|min:1',
            'details.*.harga' => 'required|numeric|min:0',
            'ppn_mode' => 'required|in:ppn,non',
        ]);

        DB::beginTransaction();

        try {

            // ambil semua DN
            $dns = DeliveryNote::with('order.customer')
                ->whereIn('id', $request->delivery_note_ids)
                ->get();

            if ($dns->isEmpty()) {
                throw new \Exception('Delivery Note tidak ditemukan.');
            }

            // ambil customer dari DN pertama
            $customer_id = $dns->first()->order->customer->id ?? null;
            $no_so = $dns->first()->order->no ?? null;

            if (!$customer_id) {
                throw new \Exception('Customer tidak ditemukan pada Delivery Note.');
            }

            // validasi semua DN customer harus sama
            foreach ($dns as $dn) {
                if (($dn->order->customer->id ?? null) != $customer_id) {
                    throw new \Exception('Semua Delivery Note harus dari customer yang sama.');
                }
            }

            // hitung total
            $dpp = collect($request->details)->sum(function ($item) {
                return $item['qty'] * $item['harga'];
            });

            $mode = $request->ppn_mode;

            $ppn = 0;

            if ($mode === 'ppn') {
                $ppn = $dpp * 0.11;
            }

            $total = $dpp + $ppn;

            $invoiceNumber = generateDocumentNumber('invoices', 'PCS-INV', 'out');

            // simpan invoice
            $invoice = Invoice::create([
                'no' => $invoiceNumber,
                'no_so' => $no_so,
                'tgl' => $request->tgl,
                'jatuh_tempo' => $request->jatuh_tempo,
                'customer_id' => $customer_id,
                'supplier_id' => null,
                'dpp' => $dpp,
                'ppn' => $ppn,
                'grand_total' => $total,
                'status' => 'unpaid',
                'paid' => 0,
                'type' => Invoice::TYPE_KELUAR,
            ]);

            // attach delivery notes (pivot)
            $invoice->deliveryNote()->attach($request->delivery_note_ids);

            // simpan detail
            foreach ($request->details as $item) {

                $invoice->details()->create([
                    'order_detail_id' => $item['order_detail_id'],
                    'qty' => $item['qty'],
                    'subtotal' => $item['qty'] * $item['harga'],
                ]);

            }

            // =======================
            // ONGKIR (tidak berubah)
            // =======================

            if ($request->filled('ongkir_nominal') && $request->ongkir_nominal > 0) {

                $ongkir = InvoiceOngkir::create([
                    'invoice_id' => $invoice->id,
                    'no' => $request->ongkir_no,
                    'nominal' => $request->ongkir_nominal,
                    'keterangan' => $request->ongkir_keterangan,
                ]);

                // $saldoTerakhir = Kas::latest('id')->value('saldo') ?? 0;
                // $saldoBaru = $saldoTerakhir - $ongkir->nominal;

                // Kas::create([
                //     'tanggal' => $request->tgl,
                //     'no_transaksi' => $ongkir->no,
                //     'keterangan' => 'Ongkir ke customer dengan no ongkir ' . $ongkir->no,
                //     'debit' => 0,
                //     'kredit' => $ongkir->nominal,
                //     'saldo' => $saldoBaru,
                //     'jenis' => 'pendanaan',
                // ]);
            }

            DB::commit();

            return redirect()
                ->route('penjualan.invoice.index')
                ->with('success', 'Invoice berhasil dibuat.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withErrors($e->getMessage())
                ->withInput();
        }
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

    public function detailSales($id)
    {
        $invoice = Invoice::with([
            'deliveryNote', // kalau masih butuh tampil SJ
            'details.orderDetail.barang',
            'deliveryNote.order.customer',
            'ongkirs'
        ])->findOrFail($id);

        return view('penjualan.invoice.detail', compact('invoice'));
    }
    public function detailPurchase($id)
    {
        $invoice = Invoice::with([
            'deliveryNote', // kalau masih butuh tampil SJ
            'details.orderDetail.barang',
            'deliveryNote.order.customer',
            'ongkirs'
        ])->findOrFail($id);

        return view('pembelian.invoice.detail', compact('invoice'));
    }

    public function print($id)
    {
        $invoice = Invoice::with([
            'deliveryNote', // kalau masih butuh tampil SJ
            'details.orderDetail.barang',
            'deliveryNote.order.customer',
            'ongkirs'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('penjualan.invoice.print', compact('invoice'));

        $filename = 'invoice-' . str_replace(['/', '\\'], '-', $invoice->no) . '.pdf';

        return $pdf->stream($filename);
    }
    public function printDot($id)
    {
        $invoice = Invoice::with([
            'deliveryNote', // kalau masih butuh tampil SJ
            'details.orderDetail.barang',
            'deliveryNote.order.customer',
            'ongkirs'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('penjualan.invoice.printDot', compact('invoice'));

        $filename = 'invoice-' . str_replace(['/', '\\'], '-', $invoice->no) . '.pdf';

        return $pdf->stream($filename);
    }
    public function printOngkir($id)
    {

        $invoice = Invoice::with([
            'deliveryNote.order.customer',
            'ongkirs'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('penjualan.invoice.printOngkir', compact('invoice'));

        $filename = 'invoice-ongkir-' . str_replace(['/', '\\'], '-', $invoice->no) . '.pdf';

        return $pdf->stream($filename);
    }


    public function getDeliveryNoteDetail($id)
    {
        $dn = DeliveryNote::with('details.orderDetail.barang', 'order.customer', 'order.supplier')->findOrFail($id);

        $items = $dn->details->map(fn($d) => [
            'barang_id' => $d->orderDetail->barang->id,
            'order_detail_id' => $d->orderDetail->id,
            'nama_barang' => $d->orderDetail->barang->nama_barang,
            'qty' => $d->qty, // pakai qty yang dikirim
            'harga' => $d->orderDetail->harga, // INI WAJIB
            'supplier_name' => $dn->order->supplier->nama_supplier ?? '',
            'customer_name' => $dn->order->customer->nama_customer ?? '',
        ]);


        return response()->json($items);
    }
}
