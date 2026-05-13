@extends('layouts.admin')

@section('content')
<div class="container">
    <h4>Input Transaksi Kas</h4>

    <form action="{{ route('petty_cash.kas.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>No Transaksi</label>
            <input type="text" name="no_transaksi" class="form-control">
        </div>

        <div class="mb-3">
            <label>Keterangan</label>
            <input type="text" name="keterangan" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Jenis Kas</label>
            <select name="jenis" class="form-select">
                <option value="petty_cash">Petty Cash</option>
                <option value="operasional">Operasional</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Debit (Kas Masuk)</label>
            <input type="number" name="debit" class="form-control">
        </div>

        <div class="mb-3">
            <label>Kredit (Pengeluaran)</label>
            <input type="number" name="kredit" class="form-control">
        </div>

        <button class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection