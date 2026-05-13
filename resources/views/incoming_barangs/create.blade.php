@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Tambah Incoming Barang</h5>
    </div>

    <form action="{{ route('incoming-barangs.store') }}" method="POST">
        @csrf
        <div class="card-body">
        {{-- <div class="row">
        <div class="col-md-4"> --}}
            <div class="form-group">
                <label>Tanggal Masuk</label>
                <input type="date" name="tgl_masuk" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Barang</label>
                <select name="barang_id" class="form-control" required>
                    <option value="">-- Pilih Barang --</option>
                    @foreach($barangs as $b)
                        <option value="{{ $b->id }}">
                            {{ $b->kode_barang }} - {{ $b->nama_barang }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Supplier</label>
                <select name="supplier_id" class="form-control" required>
                    <option value="">-- Pilih Supplier --</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">
                            {{ $s->nama_supplier }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- TAMBAHAN BARU --}}
            <div class="form-group">
                <label>No. Surat Jalan</label>
                <input type="text" name="no_sj" class="form-control">
            </div>

            <div class="form-group">
                <label>No. Invoice</label>
                <input type="text" name="no_invoice" class="form-control">
            </div>
            {{-- END TAMBAHAN --}}

            <div class="form-group">
                <label>Qty</label>
                <input type="number" name="qty" class="form-control" value="1" required>
            </div>

            <div class="form-group">
                <label>Harga</label>
                <input type="number" name="harga" class="form-control" step="0.01" required>
            </div>
        </div>

        <div class="card-footer">
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('incoming-barangs.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection
