<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: "Courier New", monospace;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 3px;
        }

        .header td {
            border: none;
        }

        .items th {
            border-bottom: 1px dashed black;
            text-align: left;
        }

        .items td {
            border-bottom: 1px dotted #999;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary td {
            border: none;
        }

        @media print {
            body {
                margin: 10px;
            }
        }
    </style>

</head>

<body>

    <table class="header">
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

    <center>
        <b>FAKTUR PENJUALAN</b>
    </center>

    <br>

    <table class="header">

        <tr>

            <td width="50%">

                <table>
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

                <table>
                    <tr>
                        <td width="90">No PO</td>
                        <td width="10">:</td>
                        <td>{{ $invoice->no_so }}</td>
                    </tr>

                    <tr>
                        <td>Tanggal</td>
                        <td>:</td>
                        <td>{{ $invoice->tgl->format('d-m-Y') }}</td>
                    </tr>
                </table>

            </td>

        </tr>

    </table>

    <br>

    @php
        $subtotal = 0;
    @endphp

    <table class="items">

        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th>Nama Barang</th>
                <th width="10%" class="text-center">Qty</th>
                <th width="20%" class="text-right">Harga</th>
                <th width="20%" class="text-right">Total</th>
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
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $detail->orderDetail->barang->nama_barang }}</td>
                    <td>{{ $qty }}</td>
                    <td>{{ number_format($harga, 0, ',', '.') }}</td>
                    <td>{{ number_format($total, 0, ',', '.') }}</td>
                </tr>

            @endforeach

        </tbody>

    </table>

    <br>

    @php
        $ppn = $invoice->ppn;
        $grandTotal = $invoice->grand_total;
    @endphp

    <table class="summary">

        <tr>
            <td width="70%"></td>
            <td width="30%">

                <table>

                    <tr>
                        <td>DPP</td>
                        <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                    </tr>

                    <tr>
                        <td>PPN 11%</td>
                        <td>Rp {{ number_format($ppn, 0, ',', '.') }}</td>
                    </tr>

                    <tr>
                        <td><b>Total</b></td>
                        <td>
                            <b>Rp {{ number_format($grandTotal, 0, ',', '.') }}</b>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>

    </table>

    <br><br>

    <table class="header">

        <tr>

            <td width="70%">

                Jumlah Yang Harus Dibayar
                # {{ trim(\App\Helpers\Terbilang::make($grandTotal)) }} Rupiah

                <br><br>

                Pembayaran ditransfer ke:<br>

                <b>{{ config('company.nama') }}</b><br>
                {{ config('company.bank') }}<br>
                {{ config('company.bank_rekening') }}

            </td>

            <td width="30%" style="text-align:center">

                Hormat Kami<br><br><br><br><br><br>

                {{ config('company.nama') }}

            </td>

        </tr>

    </table>

</body>

</html>