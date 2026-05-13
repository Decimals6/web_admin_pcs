@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">Tambah Customer</div>

    <form action="{{ route('customers.store') }}" method="POST">
        @csrf
        <div class="card-body">

            <div class="form-group">
                <label>Kode Customer</label>
                <input type="text" name="kode_customer" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Nama Customer</label>
                <input type="text" name="nama_customer" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control">
            </div>

            <div class="form-group">
                <label>Telepon</label>
                <input type="text" name="telepon" class="form-control">
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat" class="form-control"></textarea>
            </div>

        </div>

        <div class="card-footer">
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection
