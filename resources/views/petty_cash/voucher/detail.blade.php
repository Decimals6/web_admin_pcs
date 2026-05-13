<div class="mb-3">
    <table class="table table-bordered">
        <tr>
            <th>No Voucher</th>
            <td>{{ $voucher->no }}</td>
        </tr>
        <tr>
            <th>Tanggal Mulai</th>
            <td>{{ \Carbon\Carbon::parse($voucher->tgl_mulai)->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <th>Tanggal Akhir</th>
            <td>{{ \Carbon\Carbon::parse($voucher->tgl_akhir)->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <th>Total</th>
            <td><strong>{{ number_format($voucher->total) }}</strong></td>
        </tr>
    </table>
</div>

<h6>Detail Kas</h6>

<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>No Transaksi</th>
            <th>Keterangan</th>
            <th class="text-end">Debit</th>
            <th class="text-end">Kredit</th>
        </tr>
    </thead>
    <tbody>
        @foreach($voucher->kas as $kas)
            <tr>
                <td>{{ $kas->tanggal }}</td>
                <td>{{ $kas->no_transaksi }}</td>
                <td>{{ $kas->keterangan }}</td>
                <td class="text-end">{{ number_format($kas->debit) }}</td>
                <td class="text-end">{{ number_format($kas->kredit) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="mt-3">
    <a href="{{ route('petty_cash.voucher.print', $voucher->id) }}" target="_blank" class="btn btn-primary">
        Print PDF
    </a>
</div>