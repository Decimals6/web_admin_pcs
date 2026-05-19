
<h5 class="modal-title">
    Detail SO - {{ $po->no }}
</h5>
<div class="mb-3">
    <table class="table table-sm table-borderless">
        <tr>
            <th width="150">No. SO</th>
            <td>{{ $po->no }}</td>
        </tr>
        <tr>
            <th>Tanggal</th>
            <td>{{ $po->tgl->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <th>Customer</th>
            <td>{{ $po->customer->nama_customer ?? '-' }}</td>
        </tr>
        <tr>
            <th>DPP</th>
            <td>{{ number_format($po->dpp, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Pajak</th>
            <td>{{ number_format($po->pajak, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Total</th>
            <td><strong>{{ number_format($po->total, 2, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <th>Keterangan</th>
            <td>{{ $po->keterangan ?? '-' }}</td>
        </tr>
    </table>
</div>

<hr>

<h6>Detail Barang</h6>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Barang</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($po->details as $detail)
        <tr>
            <td>{{ $detail->barang->nama_barang }}</td>
            <td>{{ $detail->qty }}</td>
            <td>{{ number_format($detail->harga,2,',','.') }}</td>
            <td>{{ number_format($detail->subtotal,2,',','.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
