@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Master Bank</h2>
            <a href="{{ route('banks.create') }}" class="btn btn-primary">
                + Tambah Bank
            </a>
        </div>

        {{-- SEARCH --}}
        <form method="GET" action="{{ route('banks.index') }}" class="mt-3">
            <div class="input-group">
                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Cari kode / nama bank / rekening..."
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
                    <th>Nama Bank</th>
                    <th>Nama Rekening</th>
                    <th style="white-space: nowrap;">No Rekening</th>
                    <th style="width:170px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banks as $b)
                <tr>
                    <td style="white-space: nowrap;">
                        {{ $b->kode_bank }}
                    </td>
                    <td>{{ $b->nama_bank }}</td>
                    <td>{{ $b->nama_rekening ?? '-' }}</td>
                    <td style="white-space: nowrap;">
                        {{ $b->no_rekening ?? '-' }}
                    </td>

                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('banks.edit', $b->id) }}"
                               class="btn btn-warning btn-sm">
                                Edit
                            </a>

                            <form action="{{ route('banks.destroy', $b->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Hapus bank?')">
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
                        Data bank belum tersedia
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- INFO + PAGINATION --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Menampilkan {{ $banks->firstItem() ?? 0 }}
                –
                {{ $banks->lastItem() ?? 0 }}
                dari {{ $banks->total() }} data
            </div>

            <div>
                {{ $banks->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
