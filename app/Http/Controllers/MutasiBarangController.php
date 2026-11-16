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
        $search = $request->search;
        $statusStok = $request->status_stok;
        $from = $request->from;
        $to = $request->to;
        $sumber = $request->sumber;

        // Semua barang untuk dropdown Select2
        $barangs = Barang::with('supplier')->orderBy('nama_barang')->get();

        // Ringkasan KPI Stok Keseluruhan
        $totalItem = $barangs->count();
        $totalStokFisik = $barangs->sum('stok');
        $stokMenipisCount = $barangs->filter(fn($b) => $b->stok > 0 && $b->stok <= 10)->count();
        $stokKosongCount = $barangs->filter(fn($b) => $b->stok <= 0)->count();

        $selectedBarang = null;
        $mutasis = collect();
        $saldoAkhirMutasi = 0;
        $totalMasuk = 0;
        $totalKeluar = 0;
        $stokList = null;

        if ($barangId) {
            $selectedBarang = Barang::with('supplier')->find($barangId);

            $saldo = 0;
            $allMutasi = MutasiBarang::with(['deliveryNoteDetail.deliveryNote', 'sampel.customer'])
                ->where('barang_id', $barangId)
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

                    // Fallback deteksi sumber untuk data historis sebelum kolom sumber ada
                    if (empty($mutasi->sumber) || $mutasi->sumber === 'manual') {
                        if ($mutasi->delivery_note_detail_id) {
                            $mutasi->sumber_tampil = $mutasi->tipe === 'IN' ? 'pembelian' : 'penjualan';
                        } elseif ($mutasi->sampel_id || str_starts_with(strtoupper($mutasi->keterangan ?? ''), 'SAMPEL')) {
                            $mutasi->sumber_tampil = 'sampel';
                        } else {
                            $mutasi->sumber_tampil = $mutasi->sumber ?: 'manual';
                        }
                    } else {
                        $mutasi->sumber_tampil = $mutasi->sumber;
                    }

                    return $mutasi;
                });

            $saldoAkhirMutasi = $saldo;

            // Terapkan filter periode & sumber SETELAH saldo berjalan dihitung agar saldo tetap akurat
            $filteredMutasi = $allMutasi;

            if ($from) {
                $filteredMutasi = $filteredMutasi->filter(fn($m) => $m->tgl_mutasi >= $from);
            }
            if ($to) {
                $filteredMutasi = $filteredMutasi->filter(fn($m) => $m->tgl_mutasi <= $to);
            }
            if ($sumber) {
                $filteredMutasi = $filteredMutasi->filter(fn($m) => $m->sumber_tampil === $sumber);
            }

            $totalMasuk = $filteredMutasi->sum('masuk');
            $totalKeluar = $filteredMutasi->sum('keluar');

            // Urutkan terbaru di atas untuk tampilan atau tetap kronologis; pertahankan kronologis agar saldo mudah dibaca
            $filteredMutasi = $filteredMutasi->values();

            $perPage = 15;
            $currentPage = (int) $request->get('page', 1);
            $items = $filteredMutasi->slice(($currentPage - 1) * $perPage, $perPage)->values();

            $mutasis = new LengthAwarePaginator(
                $items,
                $filteredMutasi->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
        } else {
            // Mode Monitor Stok Keseluruhan Barang
            $stokQuery = Barang::with('supplier')->orderBy('nama_barang');

            if ($search) {
                $stokQuery->where(function ($q) use ($search) {
                    $q->where('nama_barang', 'like', '%' . $search . '%')
                        ->orWhere('kode_barang', 'like', '%' . $search . '%')
                        ->orWhereHas('supplier', function ($s) use ($search) {
                            $s->where('nama_supplier', 'like', '%' . $search . '%');
                        });
                });
            }

            if ($statusStok === 'aman') {
                $stokQuery->where('stok', '>', 10);
            } elseif ($statusStok === 'menipis') {
                $stokQuery->where('stok', '>', 0)->where('stok', '<=', 10);
            } elseif ($statusStok === 'kosong') {
                $stokQuery->where('stok', '<=', 0);
            }

            $stokList = $stokQuery->paginate(15)->withQueryString();
        }

        return view('mutasi_barangs.index', compact(
            'mutasis',
            'barangs',
            'barangId',
            'selectedBarang',
            'saldoAkhirMutasi',
            'totalMasuk',
            'totalKeluar',
            'stokList',
            'totalItem',
            'totalStokFisik',
            'stokMenipisCount',
            'stokKosongCount'
        ));
    }

    public function create(Request $request)
    {
        $barangs = Barang::orderBy('nama_barang')->get();
        $selectedBarangId = $request->barang_id;

        return view('mutasi_barangs.create', compact('barangs', 'selectedBarangId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tgl_mutasi' => 'required|date',
            'barang_id' => 'required|exists:barangs,id',
            'qty' => 'required|integer|min:1',
            'tipe' => 'required|in:IN,OUT',
            'keterangan' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $barang = Barang::findOrFail($request->barang_id);

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
                'sumber' => 'manual',
                'keterangan' => $request->keterangan ?? 'Penyesuaian Stok Manual',
            ]);

            if ($mutasi->tipe === 'IN') {
                $barang->increment('stok', $mutasi->qty);
            } else {
                $barang->decrement('stok', $mutasi->qty);
            }

            DB::commit();

            return redirect()->route('mutasi-barangs.index', ['barang_id' => $barang->id])
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
            $barangId = $mutasiBarang->barang_id;
            $barang = $mutasiBarang->barang;

            if ($mutasiBarang->tipe === 'IN') {
                $barang->decrement('stok', $mutasiBarang->qty);
            } else {
                $barang->increment('stok', $mutasiBarang->qty);
            }

            $mutasiBarang->delete();

            DB::commit();

            return redirect()->route('mutasi-barangs.index', ['barang_id' => $barangId])
                ->with('success', 'Mutasi barang berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
