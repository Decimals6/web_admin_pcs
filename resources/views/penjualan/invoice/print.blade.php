<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 5px;
        }

        .no-border td {
            border: none;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>

    <table class="no-border">
        <tr>

            <td width="60%">
                <b>{{ config('company.nama') }}</b><br>
                {{ config('company.alamat') }}<br>
                {{ config('company.kecamatan') }}<br>
                {{ config('company.provinsi') }}
            </td>

            <td width="40%">
                Kepada Yth,<br>
                <b>{{ $invoice->customer->nama_customer }}</b><br>
                {{ $invoice->customer->alamat }}
            </td>

        </tr>
    </table>

    <br>

    <h3 style="text-align:center;">FAKTUR PENJUALAN</h3>

    <table class="no-border">
        <tr>

            <td width="50%">

                <table class="no-border">
                    <tr>
                        <td width="90">No SJ</td>
                        <td width="10">:</td>
                        <td>
                            @foreach($invoice->deliveryNote as $dn)
                                {{ $dn->no }}@if(!$loop->last)<br>@endif
                            @endforeach
                        </td>
                    </tr>

                    <tr>
                        <td>No Faktur</td>
                        <td>:</td>
                        <td>{{ $invoice->no }}</td>
                    </tr>
                </table>

            </td>

            <td width="50%">

                <table class="no-border">
                    <tr>
                        <td width="110">No PO</td>
                        <td>: {{ $invoice->no_so }}</td>
                    </tr>

                    <tr>
                        <td>Tanggal Invoice</td>
                        <td>: {{ $invoice->tgl->translatedFormat('d F Y') }}</td>
                    </tr>
                </table>

            </td>

        </tr>
    </table>

    <br>

    @php
        $subtotal = 0;
        $no = 1;
    @endphp

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Barang</th>
                <th width="10%">Jumlah</th>
                <th width="20%">Harga Satuan (Rp)</th>
                <th width="20%">Total (Rp)</th>
            </tr>
        </thead>

        <tbody>

            @foreach($invoice->details as $detail)

                @php
                    $harga = $detail->orderDetail->harga ?? 0;
                    $qty = $detail->qty ?? 0;
                    $total = $qty * $harga;
                    $subtotal += $total;
                @endphp

                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $detail->orderDetail->barang->nama_barang }}</td>
                    <td class="text-right">{{ $qty }}</td>
                    <td class="text-right">{{ number_format($harga, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($total, 0, ',', '.') }}</td>
                </tr>

            @endforeach

        </tbody>
    </table>

    <br>

    @php
        $ppn = $invoice->ppn;
        $grandTotal = $invoice->grand_total;
    @endphp

    <table class="no-border">
        <tr>
            <td width="60%"></td>
            <td width="40%">

                <table>
                    <tr>
                        <td>Dasar Pengenaan Pajak</td>
                        <td>
                            Rp {{ number_format($subtotal, 0, ',', '.') }}
                        </td>
                    </tr>

                    <tr>
                        <td>PPN 11%</td>
                        <td>
                            Rp {{ number_format($ppn, 0, ',', '.') }}
                        </td>
                    </tr>

                    <tr>
                        <td><b>Grand Total</b></td>
                        <td>
                            <b>Rp {{ number_format($grandTotal, 0, ',', '.') }}</b>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

    <br><br>

    <table class="no-border">
        <tr>

            <td width="70%">
                Jumlah Yang Harus Dibayar # {{ trim(\App\Helpers\Terbilang::make($grandTotal)) }} Rupiah<br>
                Pembayaran ditransfer ke:<br>
                <b>{{ config('company.nama') }}</b><br>
                {{config('company.bank')}}<br>
                {{config('company.bank_rekening')}}
            </td>

            <td width="30%" style="text-align:center;">
                Hormat Kami,<br><br><br><br><br><br><br>
                {{ config('company.nama') }}
            </td>

        </tr>
    </table>

</body>

</html>