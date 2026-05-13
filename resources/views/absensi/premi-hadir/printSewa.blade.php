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

        .text-middle {
            text-align: center;
        }
    </style>
</head>

<body>

    <table class="no-border">
        <tr>

            <td width="60%">
                <b>{{ config('company.nama') }}</b><br><br>
                Sewa Kendaraan<br>
                Periode : {{$absensi->tanggal_mulai}} s/d {{$absensi->tanggal_akhir}}<br>
            </td>
        </tr>
    </table>
    <br>
    <br>
    @php
        $no = 1;
    @endphp
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Karyawan</th>
                <th width="15%">Plat Nomor</th>
                <th width="10%">Sewa/Hari</th>
                <th width="5%">Hari</th>
                <th width="10%">Jumlah</th>
                <th width="15%">Tanda Tangan</th>
            </tr>
        </thead>

        <tbody>
            @foreach($absensi->premiHadirs as $i => $premi)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $premi->user->name }}</td>
                    <td>{{ $premi->user->sewaKendaraan->nopol ?? '-' }}</td>
                    <td class="text-end">{{ number_format($premi->nominal_sewa_harian) }}</td>
                    <td class="text-center">{{ $premi->total_hadir }}</td>
                    <td class="text-end">{{ number_format($premi->subtotal_sewa) }}</td>
                    <td></td>
                </tr>
            @endforeach

            <tr>
                <td colspan="5" class="text-end"><b>TOTAL</b></td>
                <td colspan="2" class="text-end">
                    <b>Rp {{ number_format($absensi->premiHadirs->sum('subtotal_sewa')) }}</b>
                </td>
            </tr>
        </tbody>
    </table>

    <br>

    <table class="no-border" width="100%" style="margin-top:20px;">
        <tr>
            <td width="50%" style="text-align:left;">
                DIBUAT OLEH,
            </td>

            <td width="50%" style="text-align:right;">
                MENGETAHUI,
            </td>
        </tr>

        <tr>
            <td style="height:80px;"></td>
            <td></td>
        </tr>

        <tr>
            <td style="text-align:left;">
                <b>YULIATI</b>
            </td>

            <td style="text-align:right;">
                <b>TRI TJAHYONO</b>
            </td>
        </tr>
    </table>

</body>

</html>