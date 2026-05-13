@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Master Customer</h2>
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                + Tambah Customer
            </a>
        </div>

        {{-- SEARCH --}}
        <form method="GET" action="{{ route('customers.index') }}" class="mt-3">
            <div class="input-group">
                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Cari kode / nama / email / telepon..."
                       value="{{ request('search') }}">
                <button class="btn btn-secondary">Cari</button>
            </div>
        </form>
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th style="white-space: nowrap;">Kode</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th style="width:170px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                <tr>
                    <td style="white-space: nowrap;">
                        {{ $c->kode_customer }}
                    </td>
                    <td>{{ $c->nama_customer }}</td>
                    <td>{{ $c->email ?? '-' }}</td>
                    <td>{{ $c->telepon ?? '-' }}</td>

                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('customers.edit',$c->id) }}"
                               class="btn btn-warning btn-sm">
                                Edit
                            </a>

                            <form action="{{ route('customers.destroy',$c->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Hapus customer?')">
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
                    <td colspan="5" class="text-center">
                        Data customer belum tersedia
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- INFO + PAGINATION --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Menampilkan {{ $customers->firstItem() ?? 0 }}
                –
                {{ $customers->lastItem() ?? 0 }}
                dari {{ $customers->total() }} data
            </div>

            <div>
                {{ $customers->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
