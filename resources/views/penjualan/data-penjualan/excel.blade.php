<table border="1">
    <div><h2>LAPORAN PENJUALAN</h2></div>
    <thead style="background-color:#343a40;color:white;font-weight:bold;">
        <tr>
            <th>No</th>
            <th>No Invoice</th>
            <th>Tanggal</th>
            <th>Customer</th>
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
        <tr style="color: {{ $color }}; font-weight:bold;">
            <td>{{ $i+1 }}</td>
            <td>{{ $inv->no }}</td>
            <td>{{ $inv->tgl->format('d-m-Y') }}</td>
            <td>{{ $inv->customer->nama_customer ?? '-' }}</td>
            <td>{{ number_format($inv->dpp,0,',','.') }}</td>
            <td>{{ number_format($inv->ppn,0,',','.') }}</td>
            <td>{{ number_format($inv->grand_total,0,',','.') }}</td>
            <td>{{ $metode }}</td>
            <td>{{ $tglBayar }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
