@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Tambah Supplier</h5>
    </div>

    <form action="{{ route('suppliers.store') }}" method="POST">
        @csrf
        <div class="card-body">

            <div class="form-group">
                <label>Nama Supplier</label>
                <input type="text" name="nama_supplier"
                       class="form-control" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email"
                       class="form-control">
            </div>

            <div class="form-group">
                <label>Telepon</label>
                <input type="text" name="telepon"
                       class="form-control">
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat"
                          class="form-control"></textarea>
            </div>

        </div>

        <div class="card-footer">
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('suppliers.index') }}"
               class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection
