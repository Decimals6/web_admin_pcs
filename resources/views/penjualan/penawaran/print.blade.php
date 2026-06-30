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

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .underline {
            text-decoration: underline;
        }

        .spek-line {
            font-size: 11px;
            color: #333;
        }

        ul.syarat {
            margin: 0;
            padding-left: 18px;
        }

        ul.syarat li {
            margin-bottom: 2px;
        }
    </style>
</head>

<body>

    {{-- ===================== HEADER SURAT ===================== --}}
    <table class="no-border">
        <tr>
            <td width="60%">
                <b>{{ config('company.nama') }}</b><br>
                {{ config('company.alamat') }}<br>
                {{ config('company.kecamatan') }}<br>
                {{ config('company.provinsi') }}
            </td>

            <td width="40%" style="text-align:right;">
                {{ config('company.kota', 'Surabaya') }}, {{ \Carbon\Carbon::parse($penawaran->tanggal)->translatedFormat('d F Y') }}
            </td>
        </tr>
    </table>

    <br>

    <table class="no-border">
        <tr>
            <td width="60%">
                Kepada Yth,<br>
                <b>{{ $penawaran->customer->nama_customer }}</b><br>
                {{ $penawaran->customer->alamat }}
                @if($penawaran->up)
                    <br><br>
                    Up : {{ $penawaran->up }}
                @endif
            </td>
            <td width="40%"></td>
        </tr>
    </table>

    <br>

    <table class="no-border">
        <tr>
            <td style="text-align:center;">
                Hal : <span class="underline">{{ $penawaran->perihal }}</span><br>
                No : {{ $penawaran->no_penawaran }}
            </td>
        </tr>
    </table>

    <br>

    <p>
        Dengan hormat,<br>
        Bersama ini kami mengajukan penawaran {{ strtolower($penawaran->perihal) }} dengan rincian sebagaimana di bawah ini :
    </p>

    <p class="underline" style="margin-bottom:4px;">1. Spesifikasi barang dan harga</p>

    {{-- ===================== TABEL BARANG ===================== --}}
    <table>
        <thead>
            <tr>
                <th>Nama Barang</th>
                <th width="15%">Jumlah</th>
                <th width="18%">Harga Satuan</th>
                <th width="18%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penawaran->barangPenawaran as $item)
                @php
                    $hargaList = $item->hargaPenawaran;
                @endphp

                @foreach($hargaList as $i => $harga)
                    <tr>
                        @if($i === 0)
                            {{-- Baris pertama: nama barang + semua spek dirender dalam 1 sel,
                                 di-rowspan sebanyak jumlah tier harga --}}
                            <td rowspan="{{ $hargaList->count() }}">
                                <strong>{{ $item->nama_snapshot }}</strong>
                                @foreach($item->spekPenawaran as $spek)
                                    <br><span class="spek-line">{{ $spek->nama_spek }} : {{ $spek->keterangan }}</span>
                                @endforeach
                            </td>
                        @endif

                        <td class="text-right">
                            {{ number_format($harga->min_qty, 0, ',', '.') }} {{ $item->satuan }}
                        </td>
                        <td class="text-right">
                            Rp{{ number_format($harga->harga, 0, ',', '.') }}/{{ $item->satuan }}
                        </td>
                        <td class="text-right">
                            Rp {{ number_format($harga->min_qty * $harga->harga, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <br>

    {{-- ===================== SYARAT & KETENTUAN ===================== --}}
    <p class="underline" style="margin-bottom:4px;">2. Syarat dan Ketentuan</p>

    <table class="no-border">
        <tr>
            <td width="3%">a)</td>
            <td width="27%">Waktu Pengiriman</td>
            <td width="2%">:</td>
            <td>{{ config('company.syarat.waktu_pengiriman', '....... Hari setelah order konfirmasi / PO kami terima') }}</td>
        </tr>
        <tr>
            <td>b)</td>
            <td>Cara Pembayaran</td>
            <td>:</td>
            <td>{{ config('company.syarat.cara_pembayaran', '50% setelah PO kami terima dan 50% setelah barang diterima') }}</td>
        </tr>
        <tr>
            <td>c)</td>
            <td>Lain-lain</td>
            <td>:</td>
            <td>
                <ul class="syarat">
                    <li>Harga Franco {{ config('company.kota', 'Surabaya') }}</li>
                    <li>Harga dapat berubah sewaktu-waktu tanpa pemberitahuan dahulu</li>
                    <li>Penawaran berlaku selama {{ $penawaran->tanggal->diffInDays($penawaran->berlaku_hingga) ?: 7 }} hari</li>
                    <li>Harga belum termasuk PPN 11%</li>
                </ul>
            </td>
        </tr>
    </table>

    <br>

    <p>
        Demikian penawaran dari kami, atas perhatian dan kerjasamanya kami ucapkan terima kasih.
    </p>

    <br><br>

    {{-- ===================== TANDA TANGAN ===================== --}}
    <table class="no-border">
        <tr>
            <td width="50%" style="text-align:center;">
                Hormat Kami,
            </td>
            <td width="50%" style="text-align:center;">
                Disetujui,
            </td>
        </tr>

        <tr>
            <td style="text-align:center;">
                <br><br><br><br>
                <span class="underline">{{ config('company.direktur', 'Tri Tjahyono Eprijarto') }}</span><br>
                Direktur
            </td>
            <td style="text-align:center;">
                <br><br><br><br>
                (....................................)<br>
                <b>{{ $penawaran->customer->nama_customer }}</b>
            </td>
        </tr>
    </table>

</body>

</html>