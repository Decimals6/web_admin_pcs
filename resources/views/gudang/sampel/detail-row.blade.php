<a href="{{ route('sampel.print', $sampel->id) }}" target="_blank" class="btn btn-primary">
    Print PDF
</a>

<div class="mb-3">
    <strong>Nama Customer:</strong> {{ $sampel->customer->nama_customer ?? '-' }} <br>
    <strong>Tanggal Keluar:</strong> {{ \Carbon\Carbon::parse($sampel->tanggal)->format('d-m-Y') }} <br>
    <strong>Keterangan Nota:</strong> {{ $sampel->keterangan ?? '-' }}
</div>

<table class="table table-bordered table-striped">
    <thead class="bg-dark text-white">
        <tr>
            <th>Nama Produk / Barang</th>
            <th class="text-center" style="width: 150px;">Jumlah (Qty)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sampel->barangs as $barang)
            <tr>
                <td>{{ $barang->nama_barang }}</td>
                <td class="text-center"><strong>{{ $barang->pivot->jumlah }}</strong> pcs</td>
            </tr>
        @endforeach
    </tbody>
</table>