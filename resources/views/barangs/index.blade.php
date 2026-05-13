@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Master Barang</h2>
                <a href="{{ route('barang.create') }}" class="btn btn-primary">
                    + Tambah Barang
                </a>
            </div>

            {{-- FILTER SEARCH --}}
            <form method="GET" action="{{ route('barang.index') }}" class="mt-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama / kode barang..."
                        value="{{ request('search') }}">
                    <button class="btn btn-secondary">Cari</button>
                </div>
            </form>
        </div>

        <div class="card-body">
            {{-- <table class="table table-bordered table-striped" style="table-layout: fixed;"> --}}
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th style="white-space: nowrap;">Kode Barang</th>
                            <th>Nama Barang</th>
                            <th style="width: 20%;">Supplier</th>
                            <th style="width: 90px;" class="text-center">Stok</th>
                            <th style="width:120px;">Harga Jual</th>
                            <th style="width: 170px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($barangs as $b)
                            <tr>
                                <td>{{ $b->kode_barang }}</td>
                                <td>{{ $b->nama_barang }}</td>
                                <td>{{ $b->supplier->nama_supplier ?? '-' }}</td>
                                <td>{{ $b->stok }} roll</td>
                                <td>
                                    <a href="{{ route('barang.harga.index', $b->id) }}" class="btn btn-info btn-sm">
                                        Harga
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('barang.edit', $b->id) }}" class="btn btn-warning btn-sm">Edit</a>

                                        <form action="{{ route('barang.destroy', $b->id) }}" method="POST"
                                            style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus barang?')">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    Data tidak ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- PAGINATION --}}
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Menampilkan {{ $barangs->firstItem() ?? 0 }}
                        –
                        {{ $barangs->lastItem() ?? 0 }}
                        dari {{ $barangs->total() }} data
                    </div>

                    <div>
                        {{ $barangs->links() }}
                    </div>
                </div>

        </div>
    </div>
@endsection