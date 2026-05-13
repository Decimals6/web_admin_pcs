<div class="mb-3">
    <table class="table table-bordered">
        <tr>
            <th>No DN</th>
            <td>{{ $dn->no }}</td>
        </tr>
        <tr>
            <th>Tanggal</th>
            <td>{{ \Carbon\Carbon::parse($dn->tgl)->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <th>No PO</th>
            <td>{{ $dn->order?->no ?? '-' }}</td>
        </tr>
        <tr>
            <th>Supplier</th>
            <td>{{ $dn->order?->supplier?->nama_supplier ?? '-' }}</td>
        </tr>
    </table>
</div>

<h6>Detail Barang</h6>

<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th>Barang</th>
            <th>Qty</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dn->details as $detail)
            <tr>
                <td>{{ $detail->orderDetail?->barang?->nama_barang ?? '-' }}</td>
                <td>{{ $detail->qty }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
