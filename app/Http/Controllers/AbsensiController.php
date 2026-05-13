<?php
namespace App\Http\Controllers;

use App\Models\PremiUser;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Absensi;
use App\Models\AbsensiUser;
use App\Models\SewaKendaraan;
use App\Models\PremiHadir;
use App\Models\Kas;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $history = Absensi::orderBy('tanggal_mulai', 'desc')->get();

        $period = null;
        $users = [];

        
        $allUsers = User::orderBy('name')->get();

        if ($request->has(['start_date', 'end_date'])) {
            $period = CarbonPeriod::create($request->start_date, $request->end_date);
            $users = $allUsers;
        }

        return view('absensi.absen-karyawan.index', compact(
            'history',
            'period',
            'users',
            'allUsers' 
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'kehadiran' => 'required|array',
        ]);

        // Cek user yang nominal premi/sewa kosong
        $warningUsers = [];
        foreach ($request->kehadiran as $userId => $dates) {
            $user = User::find($userId); // ambil data user dari DB
            $masterPremi = PremiUser::where('user_id', $userId)->first();
            $nominalPremi = $masterPremi ? $masterPremi->nominal : null;

            $masterSewa = SewaKendaraan::where('user_id', $userId)->first();
            $nominalSewa = $masterSewa ? $masterSewa->nominal : null;

            if (is_null($nominalPremi) || is_null($nominalSewa)) {
                $warningUsers[] = [
                    'user_id' => $userId,
                    'name' => $user ? $user->name : 'User ' . $userId, // ambil nama dari tabel users
                    'nominal_premi' => $nominalPremi,
                    'nominal_sewa' => $nominalSewa,
                ];
            }
        }

        // Kalau ada warning, return JSON (buat JS handle)
        // if (!empty($warningUsers) && !$request->has('confirm')) {
        //     return response()->json([
        //         'status' => 'warning',
        //         'message' => 'Beberapa user memiliki nominal premi/sewa kosong.',
        //         'users' => $warningUsers,
        //     ]);
        // }

        // Kalau aman, lanjut simpan
        return $this->commitAbsensi($request);
    }

    // Fungsi pisah buat simpan data
    protected function commitAbsensi($request)
    {
        DB::beginTransaction();
        try {
            // 1. Simpan Header Absensi
            $absensi = Absensi::create([
                'tanggal_mulai' => $request->start_date,
                'tanggal_akhir' => $request->end_date,
                'keterangan' => "Absensi Periode {$request->start_date} s/d {$request->end_date}",
            ]);

            // Variabel penampung total keseluruhan untuk Kas
            $grandTotalPremi = 0;
            $grandTotalSewa = 0;

            foreach ($request->kehadiran as $userId => $dates) {
                // 2. Simpan Detail Hari Absensi
                foreach ($dates as $tanggal) {
                    AbsensiUser::create([
                        'absensi_id' => $absensi->id,
                        'user_id' => $userId,
                        'tanggal' => $tanggal,
                    ]);
                }

                $totalHadir = count($dates);

                // Ambil Master Nominal
                $masterPremi = PremiUser::where('user_id', $userId)->first();
                $nominalPremi = $masterPremi ? $masterPremi->nominal : 0;

                $masterSewa = SewaKendaraan::where('user_id', $userId)->first();
                $nominalSewa = $masterSewa ? $masterSewa->nominal : 0;

                // Hitung Subtotal per User
                $subtotalPremi = $totalHadir * $nominalPremi;
                $subtotalSewa = $totalHadir * $nominalSewa;

                // 3. Simpan Rekap Premi per User
                PremiHadir::create([
                    'user_id' => $userId,
                    'absensi_id' => $absensi->id,
                    'total_hadir' => $totalHadir,
                    'nominal_premi_harian' => $nominalPremi,
                    'nominal_sewa_harian' => $nominalSewa,
                    'subtotal_premi' => $subtotalPremi,
                    'subtotal_sewa' => $subtotalSewa,
                    'total_keseluruhan' => $subtotalPremi + $subtotalSewa,
                    'status' => 'pending',
                ]);

                // Tambahkan ke Grand Total untuk pencatatan Kas nanti
                $grandTotalPremi += $subtotalPremi;
                $grandTotalSewa += $subtotalSewa;
            }
            $firstUser = $absensi->premiHadirs()->with('user')->first();

            $isTraining = $firstUser && $firstUser->user->role == 'training';

            $jenisPembayaran = $isTraining ? 'GAJI' : 'Premi Hadir';

            // --- PENCATATAN KAS ---

            // A. Catat Kas untuk Total Premi Hadir
            if ($grandTotalPremi > 0) {
                $saldo1 = Kas::latest('id')->value('saldo') ?? 0;
                Kas::create([
                    'tanggal' => now(), // atau pakai $request->end_date
                    'no_transaksi' => 'KAS-PRM-' . $absensi->id . '-' . time(),
                    'keterangan' => "Pembayaran Total {$jenisPembayaran} Periode {$request->start_date} - {$request->end_date}",
                    'debit' => 0,
                    'kredit' => $grandTotalPremi,
                    'saldo' => $saldo1 - $grandTotalPremi,
                    'jenis' => 'petty_cash',
                ]);
            }

            // B. Catat Kas untuk Total Sewa Kendaraan
            if ($grandTotalSewa > 0) {
                // Ambil saldo terbaru lagi karena mungkin sudah berubah oleh transaksi premi di atas
                $saldo2 = Kas::latest('id')->value('saldo') ?? 0;
                Kas::create([
                    'tanggal' => now(),
                    'no_transaksi' => 'KAS-SWA-' . $absensi->id . '-' . time(),
                    'keterangan' => "Pembayaran Total Sewa Kendaraan Periode {$request->start_date} - {$request->end_date}",
                    'debit' => 0,
                    'kredit' => $grandTotalSewa,
                    'saldo' => $saldo2 - $grandTotalSewa,
                    'jenis' => 'petty_cash',
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Absensi berhasil disimpan dan saldo Kas telah diperbarui!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }
    public function premiIndex()
    {
        $absensis = Absensi::with('premiHadirs')
            ->latest()
            ->paginate(10);

        return view('absensi.premi-hadir.index', compact('absensis'));
    }
    public function detailPremi($id)
    {
        $absensi = Absensi::with(['premiHadirs.user'])
            ->findOrFail($id);

        return view('absensi.premi-hadir.detail', compact('absensi'));
    }

    public function getDetail($id)
    {
        $absensi = Absensi::findOrFail($id);

        $period = CarbonPeriod::create(
            $absensi->tanggal_mulai,
            $absensi->tanggal_akhir
        );

        $users = User::all();

        $absenDetails = AbsensiUser::where('absensi_id', $id)->get();


        // Kita return sebagai HTML partial agar mudah dimasukkan ke dalam modal
        return view('absensi.absen-karyawan.detail_partial', compact(
            'absensi',
            'period',
            'users',
            'absenDetails'
        ))->render();
    }

    public function printPremi($id)
    {
        $absensi = Absensi::with([
            'premiHadirs' => function ($q) {
                $q->where('nominal_premi_harian', '>', 0)
                    ->with(['user.sewaKendaraan']);
            }
        ])->findOrFail($id);

        $pdf = Pdf::loadView('absensi.premi-hadir.printPremi', compact('absensi'));

        $filename = 'premi-' .
            str_replace(['/', '\\'], '-', $absensi->tanggal_mulai) .
            '-sd-' .
            str_replace(['/', '\\'], '-', $absensi->tanggal_akhir) .
            '.pdf';

        return $pdf->stream($filename);
    }

    public function printSewa($id)
    {
        $absensi = Absensi::with([
            'premiHadirs' => function ($q) {
                $q->where('nominal_sewa_harian', '>', 0)
                    ->with(['user.sewaKendaraan']);
            }
        ])->findOrFail($id);

        $pdf = Pdf::loadView('absensi.premi-hadir.printSewa', compact('absensi'));

        $filename = 'sewa-' .
            str_replace(['/', '\\'], '-', $absensi->tanggal_mulai) .
            '-sd-' .
            str_replace(['/', '\\'], '-', $absensi->tanggal_akhir) .
            '.pdf';

        return $pdf->stream($filename);
    }
    
    public function print(Request $request)
    {
        $bulan = $request->bulan; // format: YYYY-MM
        $userIds = explode(',', $request->users);

        // 1. Tentukan range tanggal berdasarkan input bulan
        $start = Carbon::parse($bulan)->startOfMonth();
        $end = Carbon::parse($bulan)->endOfMonth();

        // 2. Ambil data User
        $users = User::whereIn('id', $userIds)->get();

        // 3. Ambil data kehadiran dari tabel DETAIL (AbsensiUser)
        // Asumsi: Tabel absensi_users punya kolom 'tanggal_hadir' atau 'tanggal'
        // Dan berelasi dengan tabel absensi (header)
        $data = [];

        foreach ($users as $user) {
            $absen = AbsensiUser::where('user_id', $user->id)
                ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->pluck('tanggal') // Pastikan nama kolomnya benar (tanggal atau tanggal_hadir?)
                ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                ->toArray();

            $data[] = [
                'user' => $user,
                'absen' => $absen
            ];
        }

        // 4. Buat list tanggal untuk kolom header di table PDF nanti
        $period = CarbonPeriod::create($start, $end);

        // 5. Load View (Hapus dd() nya)
        $pdf = Pdf::loadView('absensi.absen-karyawan.print', compact('data', 'period', 'bulan'))
              ->setPaper('a4', 'landscape'); // <--- TAMBAHKAN INI

        return $pdf->stream('rekap-absensi-' . $bulan . '.pdf');
    }
}