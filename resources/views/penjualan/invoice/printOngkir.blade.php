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
                <b>{{ $invoice->deliveryNote->order->customer->nama_customer }}</b><br>
                {{ $invoice->deliveryNote->order->customer->alamat }}
            </td>

        </tr>
    </table>

    <br>

    <h3 style="text-align:center;">FAKTUR ONGKOS KIRIM</h3>

    <table class="no-border">
        <tr>

            <td width="50%">

                <table class="no-border">
                    <tr>
                        <td width="110">No SJ</td>
                        <td>: {{ $invoice->deliveryNote->no }}</td>
                    </tr>

                    <tr>
                        <td>No Faktur</td>
                        <td>: {{ $invoice->no }}</td>
                    </tr>
                </table>

            </td>

            <td width="50%">

                <table class="no-border">
                    <tr>
                        <td width="110">No PO</td>
                        <td>: {{ $invoice->deliveryNote->order->no }}</td>
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
        $total = 0;
        $no = 1;
    @endphp

    <table>

        <thead>
            <tr>
                <th width="5%">No</th>
                <th>No Ongkir</th>
                <th>Keterangan</th>
                <th width="20%">Nominal (Rp)</th>
            </tr>
        </thead>

        <tbody>

            @foreach($invoice->ongkirs as $ongkir)

                @php
                    $total += $ongkir->nominal;
                @endphp

                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $ongkir->no ?? '-' }}</td>
                    <td>{{ $ongkir->keterangan ?? '-' }}</td>
                    <td class="text-right">
                        {{ number_format($ongkir->nominal, 0, ',', '.') }}
                    </td>
                </tr>

            @endforeach

        </tbody>

    </table>

    <br>

    <table class="no-border">
        <tr>

            <td width="60%"></td>

            <td width="40%">

                <table>

                    <tr>
                        <td><b>Total Ongkir</b></td>
                        <td>
                            <b>Rp {{ number_format($total, 0, ',', '.') }}</b>
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

                Jumlah Yang Harus Dibayar #
                {{ trim(\App\Helpers\Terbilang::make($total)) }} Rupiah<br>

                Pembayaran ditransfer ke:<br>

                <b>{{ config('company.nama') }}</b><br>
                {{ config('company.bank') }}<br>
                {{ config('company.bank_rekening') }}

            </td>

            <td width="30%" style="text-align:center;">

                Hormat Kami,<br><br><br><br><br><br><br>

                {{ config('company.nama') }}

            </td>

        </tr>
    </table>

</body>

</html>