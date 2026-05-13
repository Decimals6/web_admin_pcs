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

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <table class="no-border">
        <tr>
            <td width="60%">
                <b>{{ config('company.nama') }}</b>
            </td>
        </tr>
    </table>

    <h3 class="text-center">VOUCHER</h3>

    <table class="no-border">
        <tr>
            <td width="60%">
                <table class="no-border">
                    <tr>
                        <td width="60">NO</td>
                        <td width="10">:</td>
                        <td>{{ $voucher->no }}</td>
                    </tr>
                </table>
            </td>

            <td width="40%" class="text-right">
                <table class="no-border">
                    <tr>
                        <td width="80">TANGGAL</td>
                        <td width="10">:</td>
                        <td>
                            {{ \Carbon\Carbon::parse($voucher->created_at)->translatedFormat('d F Y') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <br>

    {{-- TABLE --}}
    @php $no = 1; @endphp
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="70%">Keterangan</th>
                <th width="5%"></th>
                <th width="20%">Jumlah</th>
            </tr>
        </thead>

        <tbody>
            @foreach($voucher->kas as $kas)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $kas->keterangan }}</td>
                    <td class="text-center">Rp</td>
                    <td class="text-right">{{ number_format($kas->kredit, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            {{-- TOTAL --}}
            <tr>
                <td colspan="3" class="text-right"><b>TOTAL</b></td>
                <td class="text-right">
                    <b>{{ number_format($voucher->total, 0, ',', '.') }}</b>
                </td>
            </tr>
        </tbody>
    </table>

    <br><br>

    {{-- FOOTER --}}
    <table class="no-border">
        <tr>
            <td class="text-center">Dibuat oleh,</td>
            <td class="text-center">Mengetahui,</td>
            <td class="text-center">Menyetujui,</td>
        </tr>

        <tr>
            <td height="80"></td>
            <td></td>
            <td></td>
        </tr>

        <tr>
            <td class="text-center">Yuliati</td>
            <td class="text-center">Tri Tjahyono</td>
            <td class="text-center">Alexa</td>
        </tr>
    </table>

</body>
</html>