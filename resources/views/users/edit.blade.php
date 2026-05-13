@extends('layouts.admin')

@section('content')
<h3>Edit User</h3>

<form method="POST" action="{{ route('users.update',$user->id) }}">
    @csrf @method('PUT')

    <div class="form-group">
        <label>Nama</label>
        <input type="text" name="name" value="{{ $user->name }}" class="form-control">
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="{{ $user->email }}" class="form-control">
    </div>

    <div class="form-group">
        <label>Password (kosongkan jika tidak diubah)</label>
        <input type="password" name="password" class="form-control">
    </div>

    <button class="btn btn-primary">Update</button>
</form>
@endsection
