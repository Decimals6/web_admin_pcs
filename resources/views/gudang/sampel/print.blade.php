<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 13px;
            line-height: 1.5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Reset default table border agar bisa kustom per kebutuhan */
        th, td {
            border: none;
            padding: 4px;
            vertical-align: top;
        }

        .header-table {
            margin-bottom: 20px;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 25px;
            text-transform: uppercase;
        }

        /* Meniru baris bergaris bawah seperti di gambar {62CDCBDD-ADC3-425E-81A4-5BE5EDCBE741}.jpg */
        .line-wrapper {
            border-bottom: 1px solid black;
            min-height: 20px;
            margin-bottom: 5px;
            padding-left: 5px;
        }

        .empty-line {
            border-bottom: 1px solid black;
            height: 20px;
            margin-bottom: 5px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
        
        .footer-section {
            margin-top: 30px;
        }
    </style>
</head>

<body>

    <!-- Header Perusahaan Bawaan Config -->
    <table class="header-table">
        <tr>
            <td width="60%">
                <b>{{ config('company.nama') }}</b><br>
                {{ config('company.alamat') }}<br>
                {{ config('company.kecamatan') }}<br>
                {{ config('company.provinsi') }}
            </td>
            <td width="40%" class="text-right">
                <!-- Sisi kanan header dikosongkan/bisa diisi logo jika ada -->
            </td>
        </tr>
    </table>

    <!-- Judul Dokumen -->
    <div class="title">Tanda Terima Sampel</div>

    <!-- Isi Form Utama -->
    <table>
        <!-- TELAH TERIMA DARI -->
        <tr>
            <td width="20%"><b>TELAH TERIMA DARI</b></td>
            <td width="2%" class="text-center">:</td>
            <td width="78%">
                <div class="line-wrapper">
                    <!-- Menampilkan nama perusahaan pengirim/config kantor -->
                    {{ config('company.nama') }}
                </div>
                <div class="empty-line"></div>
            </td>
        </tr>

        <!-- BERUPA -->
        <tr>
            <td><b>BERUPA</b></td>
            <td class="text-center">:</td>
            <td>
                @foreach($sampel->barangs as $index => $barang)
                    <div class="line-wrapper">
                        {{ $barang->nama_barang }} = {{ $barang->pivot->jumlah }} PCS
                    </div>
                @endforeach
                
                {{-- Mengisi baris kosong tambahan jika item sedikit agar estetikanya mirip --}}
                @if($sampel->barangs->count() < 2)
                    <div class="empty-line"></div>
                @endif
            </td>
        </tr>

        <!-- DI TERIMA OLEH -->
        <tr>
            <td><b>DI TERIMA OLEH</b></td>
            <td class="text-center">:</td>
            <td>
                <div class="line-wrapper">
                    {{ $sampel->customer->nama_customer ?? '-' }}
                </div>
                <div class="empty-line"></div>
            </td>
        </tr>

        <!-- KETERANGAN -->
        <tr>
            <td><b>KETERANGAN</b></td>
            <td class="text-center">:</td>
            <td>
                <div class="line-wrapper">
                    {{ $sampel->keterangan ?? 'SAMPLE' }}
                </div>
                <div class="empty-line"></div>
            </td>
        </tr>
    </table>

    <!-- Bagian Tanda Tangan Alur Bawah -->
    <table class="footer-section">
        <tr>
            <!-- Kolom Penerima (Kiri) -->
            <td width="50%" class="text-center">
                <br><br>
                <b>PENERIMA</b>
                <br><br><br><br><br>
                ( &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )
            </td>
            
            <!-- Kolom Hormat Kami (Kanan) -->
            <td width="50%" class="text-center">
                <!-- Mengambil data lokasi kota dari config/alamat dan tanggal transaksi -->
                {{ config('company.kecamatan') }}, {{ \Carbon\Carbon::parse($sampel->tanggal)->translatedFormat('d F Y') }}
                <br><br>
                <b>HORMAT KAMI</b>
                <br><br><br><br><br>
                <b style="text-decoration: underline; text-transform: uppercase;">{{ config('company.nama') }}</b>
            </td>
        </tr>
    </table>

</body>

</html>