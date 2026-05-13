<!DOCTYPE html>
<html>

<head>
    <title>Rekap Absensi</title>
    <style>
        /* Paksa PDF jadi landscape via CSS */
        @page {
            size: a4 landscape;
            margin: 1cm;
        }

        body {
            /* Ganti ini */
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
        }

        .header {
            margin-bottom: 20px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Penting: biar kolom nggak lebar-lebar sendiri */
        }

        th,
        td {
            border: 1px solid black;
            padding: 4px 2px;
            text-align: center;
            overflow: hidden;
            /* Biar teks yang kepanjangan nggak ngerusak tabel */
        }

        /* Atur Lebar Kolom Spesifik */
        .col-no {
            width: 3%;
        }

        .col-nama {
            width: 18%;
            text-align: left;
            padding-left: 5px;
            white-space: nowrap;
        }

        .col-tgl {
            width: auto;
        }

        /* Sisa lebar dibagi rata ke tanggal */

        .bg-sunday {
            background-color: #ffc7c7;
        }

        .footer {
            margin-top: 50px;
        }

        .footer table {
            border: none;
        }

        .footer td {
            border: none;
            text-align: center;
            height: 100px;
            vertical-align: bottom;
        }
    </style>
</head>

<body>

    <div class="header">
        ABSENSI KARYAWAN<br>
        PT. PILAR CERAH SEJAHTERA
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="3" class="col-no">NO</th>
                <th rowspan="3" class="col-nama">NAMA KARYAWAN</th>
                <th colspan="{{ count($period) }}">BULAN
                    {{ strtoupper(\Carbon\Carbon::parse($bulan)->translatedFormat('F Y')) }}</th>
            </tr>
            <tr>
                <th colspan="{{ count($period) }}">TANGGAL</th>
            </tr>
            <tr>
                @foreach($period as $date)
                    <th class="col-tgl {{ $date->isSunday() ? 'bg-sunday' : '' }}">
                        {{ $date->format('d') }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="col-nama">{{ $row['user']->name }}</td>
                    @foreach($period as $date)
                        <td class="{{ $date->isSunday() ? 'bg-sunday' : '' }}">
                            {{ in_array($date->format('Y-m-d'), $row['absen']) ? '✓' : '' }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <table style="width: 100%">
            <tr>
                <td style="text-align: left">
                    Dibuat oleh,<br>
                    Yuliati<br><br><br><br>
                    (...........................)
                </td>
                <td style="text-align: right">
                    Mengetahui,<br>
                    Tri Tjahyono<br><br><br><br>
                    (...........................)
                </td>
            </tr>
        </table>
    </div>

</body>

</html>