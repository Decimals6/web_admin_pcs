@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between">
        <h2>Incoming Barang</h2>
        <a href="{{ route('incoming-barangs.create') }}" class="btn btn-primary mb-3">+ Input Surat Jalan</a>
        </div>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>No</th>
                    <th>No Surat Jalan</th>
                    <th>No Invoice</th>
                    <th>Tanggal Masuk</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Supplier</th>
                    <th>Qty</th>
                    <th>HPP</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incomingBarangs as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->no_sj }}</td>
                    <td>{{ $item->no_invoice }}</td>
                    <td>{{ $item->tgl_masuk->format('d-m-Y') }}</td>
                    <td>{{ $item->barang->kode_barang }}</td>
                    <td>{{ $item->barang->nama_barang }}</td>
                    <td>{{ $item->supplier->nama_supplier }}</td>
                    <td>{{ $item->qty }} roll</td>
                    <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->qty * $item->harga, 0, ',', '.') }}</td>
                    <td>
                        <form action="{{ route('incoming-barangs.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin hapus?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
