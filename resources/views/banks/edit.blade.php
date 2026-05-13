@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">Edit Bank</div>

    <form action="{{ route('banks.update', $bank->id) }}" method="POST">
        @csrf
        @method('PUT') {{-- penting, agar Laravel tahu ini update --}}

        <div class="card-body">

            <div class="form-group">
                <label>Kode Bank</label>
                <input type="text" name="kode_bank" class="form-control" 
                       value="{{ old('kode_bank', $bank->kode_bank) }}" required>
            </div>

            <div class="form-group">
                <label>Nama Bank</label>
                <input type="text" name="nama_bank" class="form-control" 
                       value="{{ old('nama_bank', $bank->nama_bank) }}" required>
            </div>

            <div class="form-group">
                <label>Nama Rekening</label>
                <input type="text" name="nama_rekening" class="form-control" 
                       value="{{ old('nama_rekening', $bank->nama_rekening) }}">
            </div>

            <div class="form-group">
                <label>No Rekening</label>
                <input type="text" name="no_rekening" class="form-control" 
                       value="{{ old('no_rekening', $bank->no_rekening) }}">
            </div>

        </div>

        <div class="card-footer">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('banks.index') }}" class="btn btn-secondary">Kembali</a>
        </div>

    </form>
</div>
@endsection
