@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">Tambah Bank</div>

    <form action="{{ route('banks.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Kode Bank</label>
                <input type="text" name="kode_bank" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Nama Bank</label>
                <input type="text" name="nama_bank" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Nama Rekening</label>
                <input type="text" name="nama_rekening" class="form-control">
            </div>

            <div class="form-group">
                <label>No Rekening</label>
                <input type="text" name="no_rekening" class="form-control">
            </div>
        </div>

        <div class="card-footer">
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('banks.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection
