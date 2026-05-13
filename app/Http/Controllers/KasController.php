<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Kas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class KasController extends Controller
{
    public function index()
    {
        $data = Kas::orderBy('id', 'desc')->get();
        return view('petty_cash.kas.index', compact('data'));
    }

    public function getByDate(Request $request)
    {
        $data = Kas::whereBetween('tanggal', [
            $request->tgl_mulai,
            $request->tgl_akhir
        ])
            ->whereNull('voucher_id')
            ->where('debit', 0) // hanya kredit
            ->get();

        return response()->json($data);
    }

    public function create()
    {
        return view('petty_cash.kas.create');
    }

    public function store(Request $request)
    {
        $saldoTerakhir = Kas::latest('id')->value('saldo') ?? 0;

        $saldoBaru = $saldoTerakhir
            + $request->debit
            - $request->kredit;

        Kas::create([
            'tanggal' => $request->tanggal,
            'no_transaksi' => $request->no_transaksi ?: generateTransactionNumber('kas'),
            'keterangan' => $request->keterangan,
            'debit' => $request->debit ?? 0,
            'kredit' => $request->kredit ?? 0,
            'saldo' => $saldoBaru,
            'jenis' => $request->jenis,
        ]);

        return redirect()->route('petty_cash.kas.index');
    }

    public function indexVoucher()
    {
        $data = Voucher::orderBy('tgl_akhir')->orderBy('id')->get();
        return view('petty_cash.voucher.index', compact('data'));
    }

    public function createVoucher()
    {
        return view('petty_cash.voucher.create');
    }

    public function storeVoucher(Request $request)
    {
        // dd(
        //     Kas::whereIn('id', $request->kas_ids)->sum('kredit'),
        //     Kas::whereIn('id', $request->kas_ids)->get()->sum('kredit')
        // );
        $request->validate([
            'no' => 'required|unique:vouchers,no',
            'tgl_mulai' => 'required|date',
            'tgl_akhir' => 'required|date|after_or_equal:tgl_mulai',
            'kas_ids' => 'required|array|min:1'
        ]);

        DB::beginTransaction();

        try {
            // 🔥 1. Hitung total dulu (ambil kredit)
            $total = Kas::whereIn('id', $request->kas_ids)->sum('kredit');

            // 2. Buat voucher + total
            $voucher = Voucher::create([
                'no' => $request->no,
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_akhir' => $request->tgl_akhir,
                'total' => $total
            ]);

            // 3. Assign kas
            Kas::whereIn('id', $request->kas_ids)
                ->whereNull('voucher_id')
                ->update([
                    'voucher_id' => $voucher->id
                ]);

            DB::commit();

            return redirect()->route('petty_cash.voucher.index')
                ->with('success', 'Voucher berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
    public function detailVoucher($id)
    {
        $voucher = Voucher::with('kas')->findOrFail($id);

        return view('petty_cash.voucher.detail', compact('voucher'));
    }

    public function printVoucher($id)
    {
        $voucher = Voucher::with('kas')->findOrFail($id);

        $pdf = Pdf::loadView('petty_cash.voucher.print', compact('voucher'));
        $filename = str_replace(['/', '\\'], '-',$voucher->no) . '.pdf';

        return $pdf->stream($filename);
    }
}
