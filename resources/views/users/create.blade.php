@extends('layouts.admin')

@section('content')
<h3>Tambah User</h3>

<form method="POST" action="{{ route('users.store') }}">
    @csrf

    <div class="form-group">
        <label>Nama</label>
        <input type="text" name="name" class="form-control">
    </div>

    <div class="form-group">
    <label>Role</label>
    <select name="role" class="form-control">
        <option value="admin">Admin</option>
        <option value="staff">Staff</option>
    </select>
</div>


    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control">
    </div>

    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control">
    </div>

    <button class="btn btn-success">Simpan</button>
</form>
@endsection
