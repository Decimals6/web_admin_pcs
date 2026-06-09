<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Kas;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPenjualan = Invoice::where('type', 'out')->sum('grand_total');
        $totalPembelian = Invoice::where('type', 'in')->sum('grand_total');

        // $totalPiutang = Invoice::where('type', 'out')
        //     ->sum(DB::raw('grand_total - paid'));

        $totalPiutang = Invoice::with('paymentDetails')
            ->where('type', 'out')
            ->get()
            ->sum(function ($inv) {

                $paid = $inv->paymentDetails->sum('subtotal');

                $barangPaid = min($paid, $inv->grand_total);

                return $inv->grand_total - $barangPaid;
            });

        $totalHutang = Invoice::where('type', 'in')
            ->with('paymentDetails')
            ->get()
            ->sum(function ($inv) {
                return $inv->grand_total - $inv->paymentDetails->sum('subtotal');
            });

        $saldoKas = Kas::latest('id')->value('saldo') ?? 0;

        $currentYear = Carbon::now()->year;

        $penjualanBulanan = Invoice::selectRaw('MONTH(tgl) as bulan, SUM(grand_total) as total')
            ->where('type', 'out')
            ->whereYear('tgl', $currentYear)
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $pembelianBulanan = Invoice::selectRaw('MONTH(tgl) as bulan, SUM(grand_total) as total')
            ->where('type', 'in')
            ->whereYear('tgl', $currentYear)
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $kasMasukBulanan = Payment::selectRaw('MONTH(created_at) as bulan, SUM(total) as total')
            ->where('type', 'in')
            ->whereYear('created_at', $currentYear)
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $kasKeluarBulanan = Payment::selectRaw('MONTH(created_at) as bulan, SUM(total) as total')
            ->where('type', 'out')
            ->whereYear('created_at', $currentYear)
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        return view('dashboard', compact(
            'totalPenjualan',
            'totalPembelian',
            'totalPiutang',
            'totalHutang',
            'saldoKas',
            'penjualanBulanan',
            'pembelianBulanan',
            'kasMasukBulanan',
            'kasKeluarBulanan'
        ));
    }
}