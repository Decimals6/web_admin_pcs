@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Tambah Premi Karyawan</h5>
        </div>
        <form action="{{ route('absensi.premi-karyawan.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Nama Karyawan (Hanya yang belum punya premi)</label>
                    <select name="user_id" class="form-control" required>
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Nominal</label>
                    <input type="number" name="nominal" class="form-control" placeholder="0" required>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route('absensi.premi-karyawan.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
@endsection