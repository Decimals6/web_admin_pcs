@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Premi Karyawan</h2>
                <a href="{{ route('absensi.premi-karyawan.create') }}" class="btn btn-primary">+ Tambah Premi</a>
            </div>
            <form method="GET" action="{{ route('absensi.premi-karyawan.index') }}" class="mt-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama karyawan..."
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
                        <th>Jenis Premi</th>
                        <th>Nominal</th>
                        <th style="width: 170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($premis as $p)
                        <tr>
                            <td>{{ $p->user->name }}</td>
                            <td>{{ $p->jenis_premi }}</td>
                            <td>Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('absensi.premi-karyawan.edit', $p->id) }}"
                                    class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('absensi.premi-karyawan.destroy', $p->id) }}" method="POST"
                                    style="display:inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus data?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Data tidak ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">{{ $premis->links() }}</div>
        </div>
    </div>
@endsection