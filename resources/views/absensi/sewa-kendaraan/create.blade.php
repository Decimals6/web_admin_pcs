@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Tambah Jatah Sewa Kendaraan</h5>
        </div>
        <form action="{{ route('absensi.sewa-kendaraan.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Nama Karyawan</label>
                    <select name="user_id" class="form-control select2" required>
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">*Hanya menampilkan karyawan yang belum memiliki data sewa.</small>
                </div>

                <div class="form-group">
                    <label>Nomor Polisi (Nopol)</label>
                    <input type="text" name="nopol" class="form-control" placeholder="Contoh: L 1234 AB" required>
                </div>

                <div class="form-group">
                    <label>Jatah Per Hari (Rp)</label>
                    <input type="number" name="nominal" class="form-control" placeholder="Contoh: 25000" required>
                </div>
            </div>

            <div class="card-footer">
                <button class="btn btn-primary">Simpan Master</button>
                <a href="{{ route('absensi.sewa-kendaraan.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
@endsection