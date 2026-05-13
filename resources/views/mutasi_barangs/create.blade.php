@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Tambah Mutasi Barang</h1>

    <a href="{{ route('mutasi-barangs.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('mutasi-barangs.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="barang_id" class="form-label">Barang</label>
            <select name="barang_id" id="barang_id" class="form-control" required>
                <option value="">-- Pilih Barang --</option>
                @foreach($barangs as $b)
                    <option value="{{ $b->id }}">{{ $b->nama_barang }} ({{ $b->kode_barang }})</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="tgl_mutasi" class="form-label">Tanggal Mutasi</label>
            <input type="date" name="tgl_mutasi" id="tgl_mutasi" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="qty" class="form-label">Qty</label>
            <input type="number" name="qty" id="qty" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="tipe" class="form-label">Tipe</label>
            <select name="tipe" id="tipe" class="form-control" required>
                <option value="IN">Masuk</option>
                <option value="OUT">Keluar</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea name="keterangan" id="keterangan" class="form-control" rows="2"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection
