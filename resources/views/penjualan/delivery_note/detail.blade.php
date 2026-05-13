<a href="{{ route('dnso.print', $dn->id) }}" target="_blank" class="btn btn-primary">
    Print PDF
</a>
<a href="{{ route('dnso.printDot', $dn->id) }}" target="_blank" class="btn btn-secondary">
    Print Dot Matrix
</a>

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
            <th>Customer</th>
            <td>{{ $dn->order?->customer?->nama_customer ?? '-' }}</td>
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
