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

    public function edit($id)
    {
        $kas = Kas::findOrFail($id);
        return view('petty_cash.kas.edit', compact('kas'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string',
            'jenis' => 'required|in:petty_cash,operasional',
        ]);

        DB::beginTransaction();
        try {
            $kas = Kas::findOrFail($id);

            // Catat tanggal awal dan jenis awal sebelum diganti (untuk jaga-jaga kalau tanggal/jenis diubah)
            $tanggalLama = $kas->tanggal;
            $jenisLama = $kas->jenis;

            $kas->update([
                'tanggal' => $request->tanggal,
                'no_transaksi' => $request->no_transaksi,
                'keterangan' => $request->keterangan,
                'debit' => $request->debit ?? 0,
                'kredit' => $request->kredit ?? 0,
                'jenis' => $request->jenis,
            ]);

            // 1. Hitung ulang jalur kas lama (biar saldo di bawah tanggal lama kembali normal)
            $this->recalculateKasFrom($id, $jenisLama);

            // 2. Jika user mengubah jenis kas atau tanggal, hitung juga jalur kas yang baru
            if ($tanggalLama != $request->tanggal || $jenisLama != $request->jenis) {
                $this->recalculateKasFrom($request->id, $request->jenis);
            }

            DB::commit();
            return redirect()->route('petty_cash.kas.index')->with('success', 'Transaksi berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    // FUNGSI SAKTI PENYELAMAT SALDO BERANTAI
    private function recalculateKasFrom($idTransaksiSekarang, $jenisKas)
    {
        // 1. Ambil saldo aman terakhir TEPAT 1 baris sebelum ID yang dipilih/diinput (berdasarkan jenis kas)
        $transaksiSebelumnya = Kas::where('jenis', $jenisKas)
            ->where('id', '<', $idTransaksiSekarang)
            ->orderBy('id', 'desc') // Ambil ID terbesar yang di bawah ID sekarang
            ->first();

        $saldoBerjalan = $transaksiSebelumnya ? $transaksiSebelumnya->saldo : 0;

        // 2. Ambil semua data dari ID yang dipilih sampai ID yang paling baru/terbesar
        $transaksiAkanDiupdate = Kas::where('jenis', $jenisKas)
            ->where('id', '>=', $idTransaksiSekarang)
            ->orderBy('id', 'asc') // Urutin dari ID terkecil ke terbesar biar gulungnya berurutan
            ->get();

        // 3. Gulung ulang saldo berantai ke bawah murni berdasarkan urutan ID
        foreach ($transaksiAkanDiupdate as $row) {
            $saldoBerjalan = $saldoBerjalan + $row->debit - $row->kredit;

            // Update saldo baris ini ke database
            $row->update(['saldo' => $saldoBerjalan]);
        }
    }

    public function indexVoucher()
    {
        $data = Voucher::orderBy('id', 'desc')->paginate(10);
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

    public function deleteVoucher($id)
    {
        DB::beginTransaction();

        try {
            $voucher = Voucher::findOrFail($id);

            // 1. LEPAS GEMBOK: Balikin voucher_id di tabel kas menjadi null
            \App\Models\Kas::where('voucher_id', $voucher->id)->update([
                'voucher_id' => null
            ]);

            // 2. BABAT HABIS: Hapus data voucher utama
            $voucher->delete();

            DB::commit();

            return redirect()->route('petty_cash.voucher.index')
                ->with('success', 'Voucher berhasil dihapus dan transaksi kas telah dilepas gembok.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus voucher: ' . $e->getMessage());
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
        $filename = str_replace(['/', '\\'], '-', $voucher->no) . '.pdf';

        return $pdf->stream($filename);
    }
}
