<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Pembelian</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        h2 {
            text-align: center;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
        }

        thead {
            background: #ddd;
            font-weight: bold;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .red {
            color: red;
            font-weight: bold;
        }

        .orange {
            color: #d68910;
            font-weight: bold;
        }

        .green {
            color: green;
            font-weight: bold;
        }

        tfoot td {
            font-weight: bold;
            background: #eee;
        }
    </style>
</head>

<body>

    <h2>LAPORAN PEMBELIAN</h2>

    @php
        $totalDpp = $invoices->sum('dpp');
        $totalPpn = $invoices->sum('ppn');
        $totalPembelian = $invoices->sum('grand_total');
    @endphp

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
                <th>Metode</th>
                <th>Tgl Bayar</th>
            </tr>
        </thead>

        <tbody>

            @foreach($invoices as $i => $inv)

                @php
                    $paid = $inv->paymentDetails->sum('subtotal');
                    $sisa = $inv->grand_total - $paid;

                    if ($paid == 0) {
                        $class = 'red';
                    } elseif ($sisa > 0) {
                        $class = 'orange';
                    } else {
                        $class = 'green';
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

                <tr class="{{ $class }}">
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $inv->no }}</td>
                    <td class="text-center">{{ $inv->tgl->format('d-m-Y') }}</td>
                    <td>{{ $inv->supplier->nama_supplier ?? '-' }}</td>
                    <td class="text-right">{{ number_format($inv->dpp, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($inv->ppn, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($inv->grand_total, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $metode }}</td>
                    <td class="text-center">{{ $tglBayar }}</td>
                </tr>

            @endforeach

        </tbody>

        <tfoot>
            <tr>
                <td colspan="4" class="text-right">TOTAL PEMBELIAN</td>
                <td class="text-right">{{ number_format($totalDpp, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalPpn, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalPembelian, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>

    </table>

</body>

</html>