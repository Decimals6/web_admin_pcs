@extends('layouts.admin')

@section('title', 'Data Users')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Master Users</h2>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                + Tambah User
            </a>
        </div>

        {{-- SEARCH --}}
        <form method="GET" action="{{ route('users.index') }}" class="mt-3">
            <div class="input-group">
                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Cari nama / email..."
                       value="{{ request('search') }}">
                <button class="btn btn-secondary">Cari</button>
            </div>
        </form>
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th style="width:70px;">No</th>
                    <th>Nama User</th>
                    <th>Email</th>
                    <th style="width:170px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                <tr>
                    {{-- Nomor urut otomatis sesuai pagination --}}
                    <td>
                        {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                    </td>

                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>

                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('users.edit', $user->id) }}"
                               class="btn btn-sm btn-warning">
                                Edit
                            </a>

                            <form action="{{ route('users.destroy', $user->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin hapus user ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-sm btn-danger">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">
                        Belum ada data user
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- INFO + PAGINATION --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Menampilkan {{ $users->firstItem() ?? 0 }}
                –
                {{ $users->lastItem() ?? 0 }}
                dari {{ $users->total() }} data
            </div>

            <div>
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
