@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Edit Barang</h5>
    </div>

    <form action="{{ route('barangs.update',$barang->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="form-group">
    <label>Kode Barang</label>
    <input type="text" 
           name="kode_barang" 
           class="form-control" 
           placeholder="Contoh: BRG-001"
           required>
</div>

            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="nama_barang"
                       class="form-control" placeholder="Contoh : Label Thermal"required>
            </div>

            <div class="form-group">
                <label>Supplier</label>
                <select name="supplier_id" class="form-control">
                    <option value="">-- Pilih Supplier --</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}"
                            {{ $barang->supplier_id == $s->id ? 'selected' : '' }}>
                            {{ $s->nama_supplier }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stok"
                       class="form-control"
                       value="{{ $barang->stok }}">
            </div>

        </div>

        <div class="card-footer">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('barangs.index') }}" class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </form>
</div>
@endsection
