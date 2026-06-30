<?php

namespace App\Http\Controllers;

use App\Models\Penawaran;
use App\Models\BarangPenawaran;
use App\Models\SpekPenawaran;
use App\Models\HargaPenawaran;
use App\Models\Customer;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PenawaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Penawaran::with(['customer']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('no_penawaran', 'like', '%' . $request->search . '%')
                    ->orWhereHas('customer', function ($c) use ($request) {
                        $c->where('nama_customer', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $penawaran = $query->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('penjualan.penawaran.index', compact('penawaran'));
    }

    public function create()
    {
        $customer = Customer::all();
        $barangs = Barang::all();
        $noPenawaran = $this->generateNoPenawaran();

        return view('penjualan.penawaran.create', compact('customer', 'barangs', 'noPenawaran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_penawaran'      => 'required|string|max:50|unique:penawaran,no_penawaran',
            'perihal'           => 'required|string|max:255',
            'tanggal'           => 'required|date',
            'berlaku_hingga'    => 'nullable|date|after_or_equal:tanggal',
            'customer_id'       => 'required|exists:customers,id',
            'up'                => 'nullable|string|max:100',
            'catatan'           => 'nullable|string',

            'item'                          => 'required|array|min:1',
            'item.*.tipe'                   => 'required|in:consumable,equipment',
            'item.*.barang_id'              => 'required|exists:barangs,id',
            'item.*.satuan'                 => 'required|string|max:20',
            'item.*.keterangan'             => 'nullable|string',

            'item.*.spek'                   => 'nullable|array',
            'item.*.spek.*.nama_spek'       => 'required_with:item.*.spek|string|max:100',
            'item.*.spek.*.keterangan'      => 'required_with:item.*.spek|string|max:255',

            'item.*.harga'                  => 'required|array|min:1',
            'item.*.harga.*.min_qty'        => 'required|integer|min:1',
            'item.*.harga.*.harga'          => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $penawaran = Penawaran::create([
                'no_penawaran'   => $request->no_penawaran,
                'perihal'        => $request->perihal,
                'tanggal'        => $request->tanggal,
                'berlaku_hingga' => $request->berlaku_hingga,
                'customer_id'    => $request->customer_id,
                'up'             => $request->up,
                'status'         => 'draft',
                'catatan'        => $request->catatan,
            ]);

            foreach ($request->item as $index => $itemData) {
                $barang = Barang::findOrFail($itemData['barang_id']);

                $barangPenawaran = BarangPenawaran::create([
                    'penawaran_id'  => $penawaran->id,
                    'barang_id'     => $barang->id,
                    'nama_snapshot' => $barang->nama_barang,
                    'tipe'          => $itemData['tipe'],
                    'satuan'        => $itemData['satuan'],
                    'urutan'        => $index + 1,
                    'keterangan'    => $itemData['keterangan'] ?? null,
                ]);

                // Simpan spesifikasi (jika ada)
                if (!empty($itemData['spek'])) {
                    foreach ($itemData['spek'] as $spekIndex => $spek) {
                        SpekPenawaran::create([
                            'barang_penawaran_id' => $barangPenawaran->id,
                            'nama_spek'           => $spek['nama_spek'],
                            'keterangan'          => $spek['keterangan'],
                            'urutan'              => $spekIndex + 1,
                        ]);
                    }
                }

                // Simpan tier harga
                // Equipment dipaksa hanya simpan 1 baris pertama dengan min_qty = 1
                if ($itemData['tipe'] === 'equipment') {
                    HargaPenawaran::create([
                        'barang_penawaran_id' => $barangPenawaran->id,
                        'min_qty'             => 1,
                        'harga'               => $itemData['harga'][0]['harga'],
                    ]);
                } else {
                    foreach ($itemData['harga'] as $harga) {
                        HargaPenawaran::create([
                            'barang_penawaran_id' => $barangPenawaran->id,
                            'min_qty'             => $harga['min_qty'],
                            'harga'               => $harga['harga'],
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('penjualan.penawaran.index')
                ->with('success', 'Penawaran berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $penawaran = Penawaran::with(['barangPenawaran.spekPenawaran', 'barangPenawaran.hargaPenawaran'])
            ->findOrFail($id);

        $customer = Customer::all();
        $barangs = Barang::all();

        return view('penjualan.penawaran.edit', compact('penawaran', 'customer', 'barangs'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'no_penawaran'      => 'required|string|max:50|unique:penawaran,no_penawaran,' . $id,
            'perihal'           => 'required|string|max:255',
            'tanggal'           => 'required|date',
            'berlaku_hingga'    => 'nullable|date|after_or_equal:tanggal',
            'customer_id'       => 'required|exists:customers,id',
            'up'                => 'nullable|string|max:100',
            'catatan'           => 'nullable|string',

            'item'                          => 'required|array|min:1',
            'item.*.tipe'                   => 'required|in:consumable,equipment',
            'item.*.barang_id'              => 'required|exists:barangs,id',
            'item.*.satuan'                 => 'required|string|max:20',
            'item.*.keterangan'             => 'nullable|string',

            'item.*.spek'                   => 'nullable|array',
            'item.*.spek.*.nama_spek'       => 'required_with:item.*.spek|string|max:100',
            'item.*.spek.*.keterangan'      => 'required_with:item.*.spek|string|max:255',

            'item.*.harga'                  => 'required|array|min:1',
            'item.*.harga.*.min_qty'        => 'required|integer|min:1',
            'item.*.harga.*.harga'          => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $penawaran = Penawaran::findOrFail($id);

            $penawaran->update([
                'no_penawaran'   => $request->no_penawaran,
                'perihal'        => $request->perihal,
                'tanggal'        => $request->tanggal,
                'berlaku_hingga' => $request->berlaku_hingga,
                'customer_id'    => $request->customer_id,
                'up'             => $request->up,
                'catatan'        => $request->catatan,
            ]);

            // Jurus barbar sama seperti SO: hapus semua item lama, insert ulang.
            // Aman karena spek & harga punya cascadeOnDelete ke barang_penawaran.
            $penawaran->barangPenawaran()->delete();

            foreach ($request->item as $index => $itemData) {
                $barang = Barang::findOrFail($itemData['barang_id']);

                $barangPenawaran = BarangPenawaran::create([
                    'penawaran_id'  => $penawaran->id,
                    'barang_id'     => $barang->id,
                    'nama_snapshot' => $barang->nama_barang,
                    'tipe'          => $itemData['tipe'],
                    'satuan'        => $itemData['satuan'],
                    'urutan'        => $index + 1,
                    'keterangan'    => $itemData['keterangan'] ?? null,
                ]);

                if (!empty($itemData['spek'])) {
                    foreach ($itemData['spek'] as $spekIndex => $spek) {
                        SpekPenawaran::create([
                            'barang_penawaran_id' => $barangPenawaran->id,
                            'nama_spek'           => $spek['nama_spek'],
                            'keterangan'          => $spek['keterangan'],
                            'urutan'              => $spekIndex + 1,
                        ]);
                    }
                }

                if ($itemData['tipe'] === 'equipment') {
                    HargaPenawaran::create([
                        'barang_penawaran_id' => $barangPenawaran->id,
                        'min_qty'             => 1,
                        'harga'               => $itemData['harga'][0]['harga'],
                    ]);
                } else {
                    foreach ($itemData['harga'] as $harga) {
                        HargaPenawaran::create([
                            'barang_penawaran_id' => $barangPenawaran->id,
                            'min_qty'             => $harga['min_qty'],
                            'harga'               => $harga['harga'],
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('penjualan.penawaran.index')
                ->with('success', 'Penawaran berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $penawaran = Penawaran::findOrFail($id);
        $penawaran->delete(); // soft delete

        return redirect()->route('penjualan.penawaran.index')
            ->with('success', 'Penawaran berhasil dihapus');
    }

    public function detailPenawaran($id)
    {
        $penawaran = Penawaran::with(['customer', 'barangPenawaran.spekPenawaran', 'barangPenawaran.hargaPenawaran'])
            ->findOrFail($id);

        return response()->json($penawaran);
    }

    public function showDetailPenawaran($id)
    {
        $penawaran = Penawaran::with(['customer', 'barangPenawaran.spekPenawaran', 'barangPenawaran.hargaPenawaran'])
            ->findOrFail($id);

        return view('penjualan.penawaran.detail', compact('penawaran'));
    }

    public function print($id)
    {
        $penawaran = Penawaran::with([
            'customer',
            'barangPenawaran.spekPenawaran',
            'barangPenawaran.hargaPenawaran',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('penjualan.penawaran.print', compact('penawaran'));

        $filename = 'penawaran-' . str_replace(['/', '\\'], '-', $penawaran->no_penawaran) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Generate nomor penawaran format: {urut}/PCS-PN/{bulan_romawi}/{tahun}
     * Contoh: 001/PCS-PN/VI/2026
     *
     * PENTING: urut diambil dari nomor urut TERTINGGI yang pernah dipakai
     * di bulan & tahun berjalan (termasuk yang sudah di-soft-delete),
     * BUKAN dari count() baris yang masih ada. Kalau pakai count(),
     * penawaran yang sudah dihapus (soft delete) akan bikin nomor
     * berikutnya collision / re-use nomor yang sudah pernah dipakai.
     */
    private function generateNoPenawaran(): string
    {
        $bulanRomawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        $now = Carbon::now();
        $bulan = $bulanRomawi[$now->month];
        $tahun = $now->year;

        // withTrashed() supaya yang sudah di-soft-delete tetap dihitung
        // nomornya, jadi tidak akan di-reuse oleh penawaran baru.
        $lastNomor = Penawaran::withTrashed()
            ->where('no_penawaran', 'like', '%/PCS-PN/' . $bulan . '/' . $tahun)
            ->get()
            ->map(function ($p) {
                // Ambil angka urut di paling depan, cth dari "007/PCS-PN/VI/2026" -> 7
                preg_match('/^(\d+)\//', $p->no_penawaran, $match);
                return isset($match[1]) ? (int) $match[1] : 0;
            })
            ->max();

        $urut = str_pad(($lastNomor ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        return "{$urut}/PCS-PN/{$bulan}/{$tahun}";
    }
}