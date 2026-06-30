<?php

namespace App\Http\Controllers;

use App\Models\Sampel;
use App\Models\Customer;
use App\Models\Barang;
use App\Models\MutasiBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SampelController extends Controller
{
    public function index(Request $request)
    {
        // Eager load relasi 'customer' dan 'barangs' dari tabel pivot
        $query = Sampel::with(['customer', 'barangs']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                // Cari berdasarkan keterangan sampel
                $q->where('keterangan', 'like', '%' . $request->search . '%')
                    // Atau cari berdasarkan nama customer
                    ->orWhereHas('customer', function ($c) use ($request) {
                        $c->where('nama_customer', 'like', '%' . $request->search . '%'); // sesuaikan kolom nama di tabel customermu
                    });
            });
        }

        // Urutkan dari tanggal terbaru
        $sampels = $query->orderByDesc('tanggal')
            ->paginate(10)
            ->withQueryString();

        return view('gudang.sampel.index', compact('sampels'));
    }
    /**
     * Menampilkan Form Pembuatan Sampel
     */
    public function create()
    {
        // Ambil semua data customer dan barang untuk di-looping di select option form
        $customers = Customer::all();
        $barangs = Barang::all();

        return view('gudang.sampel.create', compact('customers', 'barangs'));
    }

    /**
     * Menyimpan Data Sampel dan Memotong Stok Barang (Many-to-Many)
     */
    public function store(Request $request)
    {
        // 1. Validasi Inputan Form
        $request->validate([
            'tanggal' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'barang_id' => 'required|array|min:1',
            'barang_id.*' => 'required|exists:barangs,id',
            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:255',
        ]);

        // 2. Mulai Database Transaction biar aman terintegrasi
        DB::beginTransaction();

        try {
            // 3. Simpan data induk ke tabel `sampels`
            $sampel = Sampel::create([
                'tanggal' => $request->tanggal,
                'customer_id' => $request->customer_id,
                'keterangan' => $request->keterangan,
            ]);

            // 4. Loop item barang untuk disimpan ke pivot dan potong stoknya
            foreach ($request->barang_id as $i => $barangId) {
                $qtyKeluar = $request->jumlah[$i];

                // [VALIDASI TAMBAHAN OPSI]: Cek apakah stok barang di gudang cukup
                $barang = Barang::findOrFail($barangId);
                if ($barang->stok < $qtyKeluar) {
                    // Jika stok kurang, batalkan transaksi dan lempar error ke catch
                    throw new \Exception("Stok barang '{$barang->nama_barang}' tidak mencukupi! Sisa stok saat ini: {$barang->stok}.");
                }

                // 5. Tempelkan data ke tabel pivot `barang_sampel`
                // Metode attach() otomatis mengisi sampel_id, barang_id, dan kolom tambahan di array kedua
                $sampel->barangs()->attach($barangId, [
                    'jumlah' => $qtyKeluar,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // 6. POTONG STOK UTAMA BARANG DI GUDANG
                $barang->decrement('stok', $qtyKeluar);

                //7. Mutasi BARANG

                MutasiBarang::create([
                    'tgl_mutasi' => $sampel->tanggal,
                    'barang_id' => $barangId,
                    'qty' => (int) $qtyKeluar,
                    'tipe' => 'OUT',
                    'keterangan' => 'SAMPEL ke ' . $sampel->customer->nama_customer,
                ]);
            }

            // Jika semua proses looping berjalan lancar tanpa interupsi, commit ke DB
            DB::commit();

            return redirect()->route('gudang.sampel.index')
                ->with('success', 'Data distribusi sampel berhasil disimpan dan stok barang otomatis dipotong!');

        } catch (\Exception $e) {
            // Jika ada satu saja yang error atau stok kurang, batalkan semua manipulasi DB di atas
            DB::rollBack();

            return redirect()->back()
                ->withInput() // Biar inputan form yang diketik user gak ilang waktu mental balik
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $sampel = Sampel::with('barangs')->findOrFail($id);
        $customers = Customer::all();
        $barangs = Barang::all();

        return view('gudang.sampel.edit', compact('sampel', 'customers', 'barangs'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'barang_id' => 'required|array|min:1',
            'barang_id.*' => 'required|exists:barangs,id',
            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $sampel = Sampel::findOrFail($id);

            // === STRATEGI EDIT STOK & MUTASI ===
            // 1. Kembalikan stok barang yang lama & Hapus mutasi lama
            foreach ($sampel->barangs as $barangLama) {
                $qtyLama = $barangLama->pivot->jumlah;
                $barangLama->increment('stok', $qtyLama);
            }

            // Hapus record mutasi barang yang terkait sampel ini sebelumnya
            MutasiBarang::where('tgl_mutasi', $sampel->tanggal)
                ->where('tipe', 'OUT')
                ->where('keterangan', 'SAMPEL ke ' . $sampel->customer->nama_customer)
                ->delete();

            // 2. Update data induk sampel
            $sampel->update([
                'tanggal' => $request->tanggal,
                'customer_id' => $request->customer_id,
                'keterangan' => $request->keterangan,
            ]);

            // 3. Array untuk sync pivot baru
            $syncData = [];

            // 4. Validasi stok baru dan kurangi stok lagi
            foreach ($request->barang_id as $i => $barangId) {
                $qtyKeluar = $request->jumlah[$i];
                $barang = Barang::findOrFail($barangId);

                if ($barang->stok < $qtyKeluar) {
                    throw new \Exception("Stok barang '{$barang->nama_barang}' tidak mencukupi! Sisa stok saat ini setelah kalkulasi ulang: {$barang->stok}.");
                }

                // Masukkan ke array sync
                $syncData[$barangId] = [
                    'jumlah' => $qtyKeluar,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // Potong stok baru
                $barang->decrement('stok', $qtyKeluar);

                // Buat mutasi baru
                MutasiBarang::create([
                    'tgl_mutasi' => $sampel->tanggal,
                    'barang_id' => $barangId,
                    'qty' => $qtyKeluar,
                    'tipe' => 'OUT',
                    'keterangan' => 'SAMPEL ke ' . $sampel->customer->nama_customer,
                ]);
            }

            // 5. Sync tabel pivot (Otomatis hapus yang lama, ganti yang baru)
            $sampel->barangs()->sync($syncData);

            DB::commit();

            return redirect()->route('gudang.sampel.index')
                ->with('success', 'Data distribusi sampel berhasil diperbarui dan stok disesuaikan kembali!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Jalur AJAX untuk render detail barang di dalam modal body
     */
    public function htmlDetail($id)
    {
        // Ambil data sampel beserta barang di pivot table-nya
        $sampel = Sampel::with('barangs')->findOrFail($id);

        // Render view partial kecil khusus untuk isi modal (mengurangi load data mentah JSON di JS)
        return view('gudang.sampel.detail-row', compact('sampel'));
    }

    public function print($id)
    {
        $sampel = Sampel::with('barangs')->findOrFail($id);

        $pdf = Pdf::loadView('gudang.sampel.print', compact('sampel'));

        $filename = 'TerimaSampel-' . str_replace(['/', '\\'], '-', $sampel->tanggal) . '.pdf';

        return $pdf->stream($filename);
    }
}