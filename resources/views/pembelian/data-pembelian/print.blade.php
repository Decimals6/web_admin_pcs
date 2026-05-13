<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pembelian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        h3 {
            margin-bottom: 5px;
        }
        .periode {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
        }
        th {
            background-color: #f0f0f0;
        }
        tfoot td {
            font-weight: bold;
            background-color: #e9ecef;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">

<h3>LAPORAN PEMBELIAN</h3>

<div class="periode">
    @if(request('from') && request('to'))
        Periode: {{ request('from') }} s/d {{ request('to') }}
    @else
        Semua Periode
    @endif
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>No Invoice</th>
            <th>Tanggal</th>
            <th>Supplier</th>
            <th>DPP</th>
            <th>PPN</th>
            <th>Total</th>
            <th>Metode Bayar</th>
            <th>Tanggal Bayar</th>

        </tr>
    </thead>
    <tbody>
        @foreach($invoices as $i => $inv)
        @php
    $paid = $inv->paymentDetails->sum('subtotal');
    $sisa = $inv->grand_total - $paid;

    if ($paid == 0) {
        $color = 'red';
    } elseif ($sisa > 0) {
        $color = 'orange';
    } else {
        $color = 'green';
    }

    $lastPaymentDetail = $inv->paymentDetails
        ->sortByDesc(fn($pd) => $pd->payment->created_at ?? null)
        ->first();

    $ket = $lastPaymentDetail?->payment?->keterangan ?? '';

    if (str_contains($ket, 'TF')) {
        $metode = 'Transfer';
    } elseif (str_contains($ket, 'Cash')) {
        $metode = 'Cash';
    } else {
        $metode = '-';
    }

    $tglBayar = $lastPaymentDetail?->payment?->created_at?->format('d-m-Y') ?? '-';
@endphp
        <tr style="color: {{ $color }};">
            <td>{{ $i+1 }}</td>
            <td>{{ $inv->no }}</td>
            <td>{{ $inv->tgl->format('d-m-Y') }}</td>
            <td>{{ $inv->supplier->nama_supplier ?? '-' }}</td>
            <td>{{ number_format($inv->dpp,0,',','.') }}</td>
            <td>{{ number_format($inv->ppn,0,',','.') }}</td>
            <td>{{ number_format($inv->grand_total,0,',','.') }}</td>
            <td>{{ $metode }}</td>
            <td>{{ $tglBayar }}</td>
        </tr>
        @endforeach
    </tbody>

    <tfoot>
        <tr>
            <td colspan="4" align="right">TOTAL</td>
            <td>{{ number_format($totalDpp,0,',','.') }}</td>
            <td>{{ number_format($totalPpn,0,',','.') }}</td>
            <td>{{ number_format($grandTotal,0,',','.') }}</td>
        </tr>
    </tfoot>
</table>

</body>
</html>
