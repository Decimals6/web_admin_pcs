<?php
use Illuminate\Support\Facades\DB;

if (!function_exists('generateDocumentNumber')) {

    function generateDocumentNumber($table, $prefix, $type = null)
    {
        $month = date('m');
        $year = date('Y');

        $romanMonths = [
            '01' => 'I','02' => 'II','03' => 'III','04' => 'IV',
            '05' => 'V','06' => 'VI','07' => 'VII','08' => 'VIII',
            '09' => 'IX','10' => 'X','11' => 'XI','12' => 'XII'
        ];

        $romanMonth = $romanMonths[$month];

        $query = DB::table($table)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);

        if ($type) {
            $query->where('type', $type);
        }

        // ambil nomor dokumen terakhir
        $lastDoc = $query->orderBy('id','desc')->value('no');

        if ($lastDoc) {
            $lastNumber = intval(substr($lastDoc, 0, 3));
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return $nextNumber.'/'.$prefix.'/'.$romanMonth.'/'.$year;
    }
}

function generateVoucherNumber($table, $prefix)
{
    $now = now();

    $day = $now->format('d');
    $month = $now->format('m');
    $year = $now->format('y'); // 2 digit

    // ambil data hari ini
    $query = DB::table($table)
        ->whereDate('created_at', $now->toDateString());

    $lastDoc = $query->orderBy('id', 'desc')->value('no');

    if ($lastDoc) {
        // ambil 2 digit terakhir (urutan)
        $lastNumber = intval(substr($lastDoc, -2));
        $nextNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
    } else {
        $nextNumber = '01';
    }

    return $prefix . $day . $month . $year . $nextNumber;
}

function generateTransactionNumber($table, $prefix = 'TRX')
{
    $last = DB::table($table)
        ->where('no_transaksi', 'like', $prefix . '%')
        ->orderBy('id', 'desc')
        ->value('no_transaksi');

    if ($last) {
        $lastNumber = intval(substr($last, strlen($prefix)));
        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $nextNumber = '0001';
    }

    return $prefix . $nextNumber;
}