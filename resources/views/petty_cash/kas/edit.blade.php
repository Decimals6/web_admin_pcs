@extends('layouts.admin')

@section('content')
    <div class="container">
        <h4>Edit Transaksi Kas</h4>

        <form action="{{ route('petty_cash.kas.update', $kas->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Tanggal</label>
                <input type="date" name="tanggal" class="form-control"
                    value="{{ \Carbon\Carbon::parse($kas->tanggal)->format('Y-m-d') }}" required>
            </div>

            <div class="mb-3">
                <label>No Transaksi</label>
                <input type="text" name="no_transaksi" class="form-control" value="{{ $kas->no_transaksi }}">
            </div>

            <div class="mb-3">
                <label>Keterangan</label>
                <input type="text" name="keterangan" class="form-control" value="{{ $kas->keterangan }}" required>
            </div>

            <div class="mb-3">
                <label>Jenis Kas</label>
                <select name="jenis" class="form-select">
                    <option value="petty_cash" {{ $kas->jenis == 'petty_cash' ? 'selected' : '' }}>Petty Cash</option>
                    <option value="operasional" {{ $kas->jenis == 'operasional' ? 'selected' : '' }}>Operasional</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Debit (Kas Masuk)</label>
                <input type="number" name="debit" class="form-control" value="{{ $kas->debit }}">
            </div>

            <div class="mb-3">
                <label>Kredit (Pengeluaran)</label>
                <input type="number" name="kredit" class="form-control" value="{{ $kas->kredit }}">
            </div>

            <button class="btn btn-success">Perbarui</button>
            <a href="{{ route('petty_cash.kas.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
@endsection