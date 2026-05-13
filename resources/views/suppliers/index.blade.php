@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Master Supplier</h2>
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                + Tambah Supplier
            </a>
        </div>

        {{-- SEARCH --}}
        <form method="GET" action="{{ route('suppliers.index') }}" class="mt-3">
            <div class="input-group">
                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Cari nama / email / telepon..."
                       value="{{ request('search') }}">
                <button class="btn btn-secondary">Cari</button>
            </div>
        </form>
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th style="width:170px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $s)
                <tr>
                    <td>{{ $s->nama_supplier }}</td>
                    <td>{{ $s->email }}</td>
                    <td>{{ $s->telepon }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('suppliers.edit',$s->id) }}"
                               class="btn btn-warning btn-sm">
                                Edit
                            </a>

                            <form action="{{ route('suppliers.destroy',$s->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Hapus supplier?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">
                        Data supplier belum tersedia
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- INFO + PAGINATION --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Menampilkan {{ $suppliers->firstItem() ?? 0 }}
                –
                {{ $suppliers->lastItem() ?? 0 }}
                dari {{ $suppliers->total() }} data
            </div>

            <div>
                {{ $suppliers->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

@endsection
