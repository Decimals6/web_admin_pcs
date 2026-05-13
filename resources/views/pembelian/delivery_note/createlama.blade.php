@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header text-white bg-dark">
    <h4>Input Surat Jalan Masuk</h4>
    </div>

    <div class="card-body">
        @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <form action="{{ route('pembelian.sj.store') }}" method="POST">
        @csrf

        <input type="hidden" name="type" value="masuk">

        <div class="row mb-3">

        <div class="col-md-3">
            <label>Tanggal</label>
            <input type="date" name="tgl" value="{{ $order->tgl->format('Y-m-d') }}">
        </div>

        <div class="col-md-3">
            <label>Supplier</label>
            <select name="supplier_id" class="form-control" required>
                <option value="">-- Pilih Supplier --</option>
                @foreach($suppliers as $sup)
                <option value="{{ $sup->id }}">{{ $sup->nama_supplier }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">No. SO</label>
            <input type="text" name="no_so" class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label">No. Invoice</label>
            <input type="text" name="no_invoice" class="form-control">
        </div>

        </div>
        <hr>

        <h5>List Item</h5>

<table class="table table-bordered" id="tableBarang">
    <thead>
        <tr>
            <th>Barang</th>
            <th width="150">Qty</th>
            <th width="100">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <select name="barang_id[]" class="form-control" required>
                    <option value="">-- Pilih Barang --</option>
                    @foreach($barangs as $barang)
                        <option value="{{ $barang->id }}">
                            {{ $barang->nama_barang }} (Stok: {{ $barang->stok }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="qty[]" class="form-control" required>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm btnRemove">
                    Hapus
                </button>
            </td>
        </tr>
    </tbody>
</table>

<button type="button" class="btn btn-success btn-sm" id="btnAdd">
    + Tambah Barang
</button>


        <button class="btn btn-success">Simpan</button>
    </form>
    </div>
</div>

<script>
document.getElementById('btnAdd').addEventListener('click', function () {

    let table = document.querySelector('#tableBarang tbody');

    let row = `
        <tr>
            <td>
                <select name="barang_id[]" class="form-control">
                    @foreach($barangs as $barang)
                        <option value="{{ $barang->id }}">
                            {{ $barang->nama_barang }} (Stok: {{ $barang->stok }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="qty[]" class="form-control" min="1">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm btnRemove">
                    Hapus
                </button>
            </td>
        </tr>
    `;

    table.insertAdjacentHTML('beforeend', row);
});

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('btnRemove')) {
        e.target.closest('tr').remove();
    }
});
</script>

@endsection
