@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Master Sewa Kendaraan (Uang Bensin)</h2>
                <a href="{{ route('absensi.sewa-kendaraan.create') }}" class="btn btn-primary">+ Tambah Data</a>
            </div>

            <form method="GET" action="{{ route('absensi.sewa-kendaraan.index') }}" class="mt-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau nopol..."
                        value="{{ request('search') }}">
                    <button class="btn btn-secondary">Cari</button>
                </div>
            </form>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Nama Karyawan</th>
                        <th>No. Polisi</th>
                        <th>Jatah Per Hari</th>
                        <th style="width: 170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sewas as $s)
                        <tr>
                            <td>{{ $s->user->name }}</td>
                            <td><span class="badge badge-info">{{ $s->nopol }}</span></td>
                            <td>Rp {{ number_format($s->nominal, 0, ',', '.') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('absensi.sewa-kendaraan.edit', $s->id) }}"
                                        class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('absensi.sewa-kendaraan.destroy', $s->id) }}" method="POST"
                                        style="display:inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Hapus data?')">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada data master sewa</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">{{ $sewas->links() }}</div>
        </div>
    </div>
@endsection