<a href="{{ route('invSales.print', $invoice->id) }}" target="_blank" class="btn btn-primary">
    <i class="fas fa-file-pdf"></i> Print PDF
</a>

<a href="{{ route('invSales.printDot', $invoice->id) }}" target="_blank" class="btn btn-secondary">
    <i class="fas fa-print"></i> Print Dot Matrix
</a>

@if(($invoice->ongkir ?? 0) > 0)
    <a href="{{ route('invSales.printOngkir', $invoice->id) }}" target="_blank" class="btn btn-info">
        <i class="fas fa-truck"></i> Print Invoice Ongkir
    </a>
@endif

<hr>

<h5>Informasi Invoice</h5>
<table class="table table-bordered">
    <tr>
        <th width="30%">No Invoice</th>
        <td>{{ $invoice->no }}</td>
    </tr>
    <tr>
        <th>Tanggal</th>
        <td>{{ $invoice->tgl ? $invoice->tgl->format('d-m-Y') : '-' }}</td>
    </tr>
    <tr>
        <th>Status</th>
        <td>
            @if($invoice->status == 'paid')
                <span class="badge bg-success">Paid</span>
            @elseif($invoice->status == 'partial')
                <span class="badge bg-warning text-dark">Partial</span>
            @else
                <span class="badge bg-danger">Unpaid</span>
            @endif
        </td>
    </tr>
</table>

<h5>Surat Jalan</h5>
<table class="table table-bordered">
    <thead>
        <tr>
            <th width="30%">Tanggal</th>
            <th>No Surat Jalan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoice->deliveryNote as $dn)
            <tr>
                <td>{{ $dn->tgl ? $dn->tgl->format('d-m-Y') : '-' }}</td>
                <td>{{ $dn->no }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="text-center text-muted">Tidak ada Surat Jalan terkait.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<hr>

<h5>Informasi Order</h5>
<table class="table table-bordered">
    <tr>
        <th width="30%">No Order (SO)</th>
        <td>{{ $invoice->no_so ?? '-' }}</td>
    </tr>
    <tr>
        <th>Customer</th>
        <td>{{ $invoice->customer->nama_customer ?? '-' }}</td>
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
            <th class="text-end" width="15%">Qty</th>
            <th class="text-end" width="20%">Harga</th>
            <th class="text-end" width="20%">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->details as $detail)
            @php
                $harga = $detail->orderDetail->harga ?? 0;
                $qty = $detail->qty ?? 0;
                $lineTotal = $detail->subtotal ?? ($qty * $harga);
                $subtotal += $lineTotal;
            @endphp
            <tr>
                <td>{{ $detail->orderDetail->barang->nama_barang ?? '-' }}</td>
                <td class="text-end">{{ number_format($qty, 2, ',', '.') }}</td>
                <td class="text-end">{{ number_format($harga, 2, ',', '.') }}</td>
                <td class="text-end">{{ number_format($lineTotal, 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<hr>

@php
    $diskon = $invoice->diskon ?? 0;
    $ppn = $invoice->ppn ?? 0;
    $ongkir = $invoice->ongkir ?? 0;
    $grandTotal = $invoice->grand_total ?? ($subtotal - $diskon + $ppn + $ongkir);
@endphp

<h5>Rincian Pembayaran</h5>
<table class="table table-bordered">
    <tr>
        <th width="70%">Subtotal (DPP)</th>
        <td class="text-end">{{ number_format($subtotal, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <th>Diskon Potongan</th>
        <td class="text-end text-danger">- {{ number_format($diskon, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <th>Pajak PPN 11%</th>
        <td class="text-end">{{ number_format($ppn, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <th>Nominal Ongkir</th>
        <td class="text-end">{{ number_format($ongkir, 2, ',', '.') }}</td>
    </tr>
    <tr class="table-dark text-white">
        <th>Grand Total</th>
        <td class="text-end fw-bold">
            {{ number_format($grandTotal, 2, ',', '.') }}
        </td>
    </tr>
</table>