<h5>Informasi Invoice</h5>
<table class="table table-bordered">
    <tr>
        <th>No Invoice</th>
        <td>{{ $invoice->no }}</td>
    </tr>
    <tr>
        <th>Tanggal</th>
        <td>{{ $invoice->tgl->format('d-m-Y') }}</td>
    </tr>
    <tr>
        <th>Status</th>
        <td>{{ $invoice->status }}</td>
    </tr>
</table>

<hr>

<h5>Informasi Order</h5>
<table class="table table-bordered">
    <tr>
        <th>No Order</th>
        <td>{{ $invoice->order->no ?? '-' }}</td>
    </tr>
    <tr>
        <th>Customer</th>
        <td>{{ $invoice->order->customer->nama_customer ?? '-' }}</td>
    </tr>
</table>

<hr>

<h5>Detail Barang</h5>

@php
    $subtotal = 0;
@endphp

<table class="table table-striped">
    <thead>
        <tr>
            <th>Nama Barang</th>
            <th class="text-end">Qty</th>
            <th class="text-end">Harga</th>
            <th class="text-end">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->details as $detail)
            @php
                $harga = $detail->orderDetail->harga ?? 0;
                $qty = $detail->qty ?? 0;
                $lineTotal = $qty * $harga;
                $subtotal += $lineTotal;
            @endphp
        <tr>
            <td>{{ $detail->orderDetail->barang->nama_barang ?? '-' }}</td>
            <td class="text-end">{{ number_format($qty, 2) }}</td>
            <td class="text-end">{{ number_format($harga, 2) }}</td>
            <td class="text-end">{{ number_format($lineTotal, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<hr>

@php
    $ppn = $invoice->ppn;
    $grandTotal = $invoice->grand_total;
@endphp

<table class="table table-bordered">
    <tr>
        <th width="70%">Subtotal (DPP)</th>
        <td class="text-end">{{ number_format($subtotal, 2) }}</td>
    </tr>
    <tr>
        <th>Pajak 11%</th>
        <td class="text-end">{{ number_format($ppn, 2) }}</td>
    </tr>
    <tr>
        <th>Grand Total</th>
        <td class="text-end fw-bold">
            {{ number_format($grandTotal, 2) }}
        </td>
    </tr>
</table>
